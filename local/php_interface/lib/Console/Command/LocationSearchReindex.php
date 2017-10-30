<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 16.08.2016
 * Time: 17:48
 */

namespace FourPaws\Console\Command;

use Bitrix\Main\Loader;
use Bitrix\Sale\Location\Admin\LocationHelper;
use Bitrix\Sale\Location\Admin\TypeHelper;
use Bitrix\Sale\Location\Search\Finder;
use Bitrix\Sale\Location\Search\ReindexProcess;
use Bitrix\Sale\Location\Util\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class LocationSearchReindex extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;
    
    /**
     * @var OutputInterface
     */
    protected $output;
    
    protected $arType = [];
    
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
        $this->setName('adv:location-search-reindex')->setDescription('Bitrix console location search reindex');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        
        $this->import();
    }
    
    protected function import()
    {
        $arParams    = $this->getArParams();
        $timeLimit   = $this->getTimeLimit();
        $initialTime = time();
        
        $process = new ReindexProcess($arParams);
        
        if (is_array($arParams['TYPES']) && count($arParams['TYPES'])) {
            Finder::setIndexedTypes($arParams['TYPES']);
        }
        
        if (is_array($arParams['LANG']) && count($arParams['LANG'])) {
            Finder::setIndexedLanguages($arParams['LANG']);
        }
        $process->reset();
        
        $this->output->writeln('Reindex start at ' . date('Y-m-d H:i:s'));
        
        while (true) {
            $percent = (int)$process->performStage();
            $process->setTimeLimit(time() - $initialTime + $timeLimit + 1);
            
            $this->output->writeln(sprintf('[%s/%s][STEP:%s]  %s',
                                           $process->getPercent(),
                                           100,
                                           $process->getStep(),
                                           $process->getStageCode()));
            
            if ($percent >= 100) {
                break;
            }
        }
        
        $this->output->writeln('Reindex end at ' . date('Y-m-d H:i:s'));
    }
    
    protected function getArParams()
    {
        $arParams = [];
        
        $arParams['LOCK'] = $this->isUseLock();
        $arParams['STEP'] = 0;
        
        if (!$this->isAllTypes()) {
            $arParams['TYPES'] = $this->getTypes();
        }
        
        if (!$this->isAllLanguage()) {
            $arParams['LANG'] = $this->getLang();
        }
        
        return $arParams;
    }
    
    protected function getTimeLimit()
    {
        $minTimeLimit = Process::MIN_TIME_LIMIT;
        $question     = new Question('Please input timelimit for iteration[' . $minTimeLimit . ']: ', $minTimeLimit);
        $question->setValidator(function ($answer) use ($minTimeLimit) {
            $answer = (int)$answer;
            if ($answer < $minTimeLimit) {
                throw new \InvalidArgumentException('Time limit mast be more then ' . $minTimeLimit);
            }
            
            return $answer;
        });
        
        return $this->ask($question);
    }
    
    protected function isUseLock()
    {
        $question = new ConfirmationQuestion('Is use lock? [y/n]', true, '/^(y|j)/i');
        
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
    
    protected function isAllTypes()
    {
        $question = new ConfirmationQuestion('Are import all types? [y/n]', true, '/^(y|j)/i');
        
        return $this->ask($question);
    }
    
    protected function getTypes()
    {
        $types = TypeHelper::getTypes();
        usort($types,
            function ($first, $second) {
                if ($first['ID'] > $second['ID']) {
                    return 1;
                } elseif ($first['ID'] < $second['ID']) {
                    return -1;
                }
                
                return 0;
            });
        
        $arCodeToId = [];
        $arChoice   = [];
        foreach ($types as $type) {
            $arChoice[$type['CODE']]   = $type['NAME_CURRENT'];
            $arCodeToId[$type['CODE']] = $type['ID'];
        }
        
        $question =
            new ChoiceQuestion('Please write a comma-separated list of types: ',
                               $arChoice,
                               implode(',', array_keys($arChoice)));
        $question->setMultiselect(true)
                 ->setErrorMessage('Type %s is invalid')
                 ->setMaxAttempts(3)
                 ->setAutocompleterValues([]);
        
        $arAnswer = $this->ask($question);
        
        $arResult = array_intersect_key($arCodeToId, array_flip($arAnswer));
        if (0 === count($arResult)) {
            throw new \RuntimeException('You should choose one of type');
        }
        
        return $arResult;
    }
    
    protected function isAllLanguage()
    {
        $question = new ConfirmationQuestion('Are all language? [y/n]', true, '/^(y|j)/i');
        
        return $this->ask($question);
    }
    
    protected function getLang()
    {
        $langs = TypeHelper::getLanguageList();
        
        $arLangs = [];
        foreach ($langs as $id => $name) {
            $arLangs[$id] = $name;
        }
        
        $question =
            new ChoiceQuestion('Please write a comma-separated list of langs: ',
                               $arLangs,
                               implode(',', array_keys($arLangs)));
        $question->setMultiselect(true)
                 ->setErrorMessage('Type %s is invalid')
                 ->setMaxAttempts(3)
                 ->setAutocompleterValues([]);
        
        $arResult = $this->ask($question);
        if (0 === count($arResult)) {
            throw new \RuntimeException('You should choose one of language');
        }
        
        return $arResult;
    }
}
