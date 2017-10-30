<?php
/**
 * Created by PhpStorm.
 * User: pinchuk
 * Date: 26.05.16
 * Time: 11:34
 */

namespace FourPaws\Console\Command;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Sale\Location\Admin\LocationHelper;
use Bitrix\Sale\Location\Import\ImportProcess;
use Bitrix\Sale\Location\Search\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class LocationImport extends Command
{
    protected $remoteLayout = [];
    
    /**
     * @var InputInterface
     */
    protected $input;
    
    /**
     * @var OutputInterface
     */
    protected $output;
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!Loader::includeModule('sale')) {
            throw new \RuntimeException('Cant include module "sale"');
        }
        
        if (!set_time_limit(0)) {
            throw new \RuntimeException('Cant disable time limit');
        }
        
        if (!ini_set('memory_limit', '1024M')) {
            throw new \RuntimeException('Cant set new memory limit');
        }
        
        if (!LocationHelper::checkLocationEnabled()) {
            throw new \RuntimeException('Locations were disabled or data has not been converted');
        }
    }
    
    protected function configure()
    {
        $this->setName('adv:location')->setDescription('Bitrix console location import');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        
        $arParams = $this->getArParams();
        
        $this->truncateReBalance();
        $this->executeImport($arParams);
    }
    
    protected function getArParams()
    {
        return $this->questionArParams();
    }
    
    protected function questionArParams()
    {
        $locationSets = [];
        
        $arOptions = [
            'SOURCE'             => 'remote',
            'PACK'               => 'standard',
            'TIME_LIMIT'         => 36000,
            'INTEGRITY_PRESERVE' => 1,
            'DEPTH_LIMIT'        => 9,
        ];
        
        $arAdditional = [];
        $arParams     = [
            'OPTIONS'       => &$arOptions,
            'LOCATION_SETS' => &$locationSets,
            'ADDITIONAL'    => &$arAdditional,
            'STEP'          => 0,
            'step'          => 0,
        ];
        
        if ($this->getSource() === 'file') {
            $arOptions['SOURCE'] = 'file';
            $fileName            = $this->selectFile();
            $type                = $this->selectFileType();
            
            $_FILES[md5($fileName)] = [
                'type'     => $type,
                'tmp_name' => $fileName,
            ];
            
            $this->getImportProcess()->saveUserFile(md5($fileName));
        } else {
            do {
                $locationSets = $this->selectCountry($locationSets);
                $this->output->writeln('Selected ' . count($locationSets) . ' elements');
            } while ($this->isSelectMoreCountry() || 0 === count($locationSets));
            
            $this->selectAdditional($arAdditional);
            $this->selectDeepLevel($arOptions);
            $this->selectPack($arOptions);
        }
        
        return $arParams;
    }
    
    protected function getSource()
    {
        $question =
            new ChoiceQuestion('Please select import source: ',
                               [
                                   'remote',
                                   'file',
                               ],
                               'remote');
        $question->setErrorMessage('Source %s is invalid')->setMaxAttempts(3);
        
        return $this->ask($question);
    }
    
    protected function ask(Question $question)
    {
        /**
         * @var QuestionHelper $helper
         */
        $helper = $this->getHelper('question');
        
        return $helper->ask($this->input, $this->output, $question);
    }
    
    protected function selectFile()
    {
        $question = new Question('Please input full file path(from /): ');
        $question->setValidator(function ($filename) {
            if (!file_exists($filename)) {
                throw new \RuntimeException('File ' . $filename . ' is not exist');
            }
            
            return $filename;
        });
        $question->setMaxAttempts(3);
        
        return $this->ask($question);
    }
    
    protected function selectFileType()
    {
        $question = new ChoiceQuestion('Please input file type(text/csv): ', [
                                                                               'text/plain',
                                                                               'text/csv',
                                                                               'application/vnd.ms-excel',
                                                                               'application/octet-stream',
                                                                           ], 'text/csv');
        $question->setMaxAttempts(2);
        
        return $this->ask($question);
    }
    
    protected function getImportProcess(array $arRequest = [])
    {
        
        /** @noinspection PhpUndefinedClassInspection */
        return new ImportProcess([
            
                                     // system parameters
                                     'INITIAL_TIME'    => (int)time(),
                                     'ONLY_DELETE_ALL' => false,
                                     'USE_LOCK'        => true,
            
                                     // parameters from the form
                                     'REQUEST'         => $arRequest,
                                     'LANGUAGE_ID'     => LANGUAGE_ID,
                                 ]);
    }
    
    protected function selectCountry($locationSets)
    {
        $layout    = $this->getRemoteLayout();
        $arCountry = [];
        $arOther   = [];
        foreach ((array)$layout[''] as $id => $data) {
            /** @noinspection TypeUnsafeComparisonInspection */
            if (array_key_exists('CODE', $data)
                && !empty($data['CODE'])
                && $data['TYPE_CODE'] == 'COUNTRY'
                && empty($data['PARENT_CODE'])) {
                if (array_key_exists($data['CODE'], $layout)) {
                    $arCountry[$data['CODE']] = $data['NAME']['RU']['NAME'];
                } else {
                    $arOther[$data['CODE']] = $data['NAME']['RU']['NAME'];
                }
            }
        }
        
        if (0 === count($arCountry)) {
            throw new \RuntimeException('Empty country list');
        }
        
        if (count($arOther) > 0) {
            $arCountry['OTHER'] = 'Мир';
        }
        
        $question = new ChoiceQuestion('Please select country that we should import: ', $arCountry);
        $question->setErrorMessage('Country %s is invalid')->setMaxAttempts(3);
        
        $country = $this->ask($question);
        
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($country == 'OTHER') {
            asort($arOther);
            
            $question = new ChoiceQuestion('Please select country that we should import: ', $arOther);
            $question->setErrorMessage('Country %s is invalid')->setMaxAttempts(3);
            
            $country = $this->ask($question);
        }
        
        $locationSets[] = $country;
        $locationSets   = array_merge($locationSets, $this->getChild($country));
        $locationSets   = array_unique($locationSets);
        
        return $locationSets;
    }
    
    protected function getRemoteLayout()
    {
        if (0 === count($this->remoteLayout)) {
            $import = $this->getImportProcess();
            $import->turnOffCache();
            $this->remoteLayout = $import->getRemoteLayout();
        }
        
        return $this->remoteLayout;
    }
    
    protected function getChild($mainParentId)
    {
        $arResult = [];
        
        $layout = $this->getRemoteLayout();
        if (array_key_exists($mainParentId, $layout) && is_array($layout[$mainParentId])) {
            foreach ($layout[$mainParentId] as $id => $data) {
                if (array_key_exists('CODE', $data) && !empty($data['CODE'])) {
                    $arResult[] = $data['CODE'];
                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $arResult = array_merge($arResult, $this->getChild($data['CODE']));
                }
            }
        }
        
        return $arResult;
    }
    
    protected function isSelectMoreCountry()
    {
        return $this->ask(new ConfirmationQuestion('Choose additional country(y/n)? ', false));
    }
    
    protected function selectAdditional(array &$arAdditional)
    {
        if ($this->isZipImport()) {
            $arAdditional['ZIP'] = 'ZIP';
        }
        
        if ($this->isYandexImport()) {
            $arAdditional['YAMARKET'] = 'YAMARKET';
        }
    }
    
    protected function isZipImport()
    {
        return $this->ask(new ConfirmationQuestion('Import ZIP Code [default: no]?', false));
    }
    
    protected function isYandexImport()
    {
        return $this->ask(new ConfirmationQuestion('Import Yandex Market Code(y/n)? ', false));
    }
    
    protected function selectDeepLevel(array &$arOptions)
    {
        $import = $this->getImportProcess();
        $import->turnOffCache();
        
        $types = $import->getTypeLevels();
        
        $arChoose = [];
        foreach ($types as $id => $level) {
            $arChoose[(string)$id] = $level['NAMES'];
        }
        
        if (0 === count($arChoose)) {
            throw new \RuntimeException('No type level was get');
        }
        
        $question = new ChoiceQuestion('Please select import deep level[default: 9]: ', $arChoose, 9);
        $question->setErrorMessage('Deep level %s is invalid')->setMaxAttempts(3);
        
        $limit = $this->ask($question);
        
        if (!array_key_exists($limit, $arChoose)) {
            $arChoose = array_flip($arChoose);
        }
        
        if (array_key_exists($limit, $arChoose)) {
            $arOptions['DEPTH_LIMIT'] = $arChoose[$limit];
        }
    }
    
    protected function selectPack(array &$arOptions)
    {
        $question =
            new ChoiceQuestion('Location pack: ',
                               [
                                   'standard',
                                   'extended',
                               ],
                               0);
        $question->setErrorMessage('Pack %s is invalid')->setMaxAttempts(3);
        
        $arOptions['PACK'] = $this->ask($question);
    }
    
    protected function truncateReBalance()
    {
        
        /** @noinspection PhpUndefinedClassInspection */
        $tableName = ImportProcess::TREE_REBALANCE_TEMP_TABLE_NAME;
        if (Application::getConnection()->isTableExists($tableName)) {
            Application::getConnection()->query("truncate table {$tableName}");
        }
    }
    
    protected function executeImport(array $arParams = [])
    {
        $this->output->writeln('Import start');
        $importProcess = $this->getImportProcess($arParams);
        $importProcess->turnOffCache();
        $step = 0;
        while (true) {
            $percent   = (int)$importProcess->performStage();
            $nextStage = $importProcess->getStageCode();
            
            if ($percent === 100) {
                $importProcess->logFinalResult();
                $importProcess->getStatisticsAll();
                
                Finder::setIndexInvalid();
                $GLOBALS['CACHE_MANAGER']->ClearByTag('sale-location-data');
                break;
            }
            
            $step++;
            $this->output->writeln(sprintf('Step: %s. Next stage: %s [%s/100]',
                                           $step,
                                           $nextStage,
                                           $percent));
        }
        $this->output->writeln('Import end');
    }
}

