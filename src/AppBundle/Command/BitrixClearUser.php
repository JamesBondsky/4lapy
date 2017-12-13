<?php

namespace FourPaws\AppBundle\Command;

use Bitrix\Main\Entity\Query;
use Bitrix\Main\UserTable;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BitrixClearUser
 *
 * @package FourPaws\AppBundle\Command
 */
class BitrixClearUser extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    const ARGUMENT_MINIMAL_ID = 'mid';
    
    /**
     * BitrixClearHighloadBlock constructor.
     *
     * @param null $name
     *
     * @throws LogicException
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setLogger(new Logger('Migrator', [new StreamHandler(STDOUT, Logger::DEBUG)]));
    }
    
    /**
     * @throws InvalidArgumentException
     */
    public function configure()
    {
        $this->setName('bitrix:clear:user')->setDescription('Clear users')->addArgument(self::ARGUMENT_MINIMAL_ID,
                                                                                        InputArgument::REQUIRED,
                                                                                        'Minimal user id. Must be an integer, greater than 4');
    }
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null
     *
     * @throws InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $minimalId = $input->getArgument(self::ARGUMENT_MINIMAL_ID);
        
        if ($minimalId < 5) {
            throw new InvalidArgumentException('mid must be an integer, greater than 4');
        }
        
        try {
            $this->removeUsers($minimalId);
            
            $this->logger->info(sprintf('Users has been delete.'));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unknown error: %s', $e->getMessage()));
        }
        
        return null;
    }
    
    /**
     * @param int $minimalId
     */
    private function removeUsers(int $minimalId)
    {
        $userIdCollection =
            (new Query(UserTable::getEntity()))->setSelect(['ID'])->setFilter(['>=ID' => $minimalId])->exec();
        
        $count = $userIdCollection->getSelectedRowsCount();
        
        $this->logger->debug(sprintf('Users count - %s', $count));
        
        while ($user = $userIdCollection->fetch()) {
            $this->removeUser($user['ID']);
            
            $this->logger->debug(sprintf('Users count - %s', $count--));
        }
    }
    
    /**
     * @param int $id
     *
     * @return bool
     */
    private function removeUser(int $id) : bool
    {
        $user = new \CUser();
        $user->Delete($id);
        
        if ($user->LAST_ERROR) {
            $this->logger->error(sprintf('User with id %s remove error: %s', $id, $user->LAST_ERROR));
        } else {
            $this->logger->debug(sprintf('User with id %s was removed', $id));
        }
        
        return !$user->LAST_ERROR;
    }
}
