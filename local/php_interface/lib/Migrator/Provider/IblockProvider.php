<?

namespace FourPaws\Migrator\Provider;

abstract class IblockProvider extends ProviderAbstract
{
    private $iblockId = 0;
    
    /**
     * @return string
     */
    public function getPrimary() : string
    {
        return 'ID';
    }
    
    /**
     * @return int
     */
    public function getIblockId() : int
    {
        return $this->iblockId;
    }
    
    /**
     * @param int $iblockId
     */
    private function setIblockId(int $iblockId)
    {
        $this->iblockId = $iblockId;
    }
    
    /**
     * ProviderIblock constructor.
     *
     * @param string $entityName
     * @param int    $iblockId
     */
    public function __construct(string $entityName, int $iblockId)
    {
        $this->setIblockId($iblockId);
        
        parent::__construct($entityName);
    }
}