<?php

namespace FourPaws\Menu\Helper;

class TreeMenuBuilder
{
    protected $menu     = [];
    
    protected $maxDepth = 4;
    
    protected $callable = [];
    
    /**
     * @param array $arResult
     * @param array $arParams
     */
    public function __construct(array $arResult = [], array $arParams = [])
    {
        $this->init($arResult, $arParams);
        $this->setDefaultMarkupFunction();
    }
    
    /**
     * @param array $arResult
     * @param array $arParams
     *
     * @return $this
     */
    protected function init(array $arResult = [], array $arParams = []) : TreeMenuBuilder
    {
        $this->setMenu($arResult)->setParams($arParams);
        
        return $this;
    }
    
    /**
     * @param $menu
     *
     * @return $this
     */
    private function setMenu(array $menu) : TreeMenuBuilder
    {
        $this->menu = self::makeTree(self::resetMenuIndexes($menu));
        
        return $this;
    }
    
    /**
     * @param $params
     *
     * @return $this
     */
    private function setParams(array $params) : TreeMenuBuilder
    {
        if (isset($params['MAX_LEVEL'])) {
            $this->maxDepth = $params['MAX_LEVEL'];
        }
        
        return $this;
    }
    
    /**
     * @param $menu
     *
     * @return array
     */
    private static function resetMenuIndexes(array $menu) : array
    {
        $result = [];
        
        foreach ($menu as $index => $item) {
            $result[] = $item;
        }
        
        return $result;
    }
    
    /**
     * Recursive makes tree from standard Bitrix $arResult
     *
     * @param array $inputMenu
     * @param int   $parentIndex
     *
     * @return array
     */
    private static function makeTree(array $inputMenu = [], int $parentIndex = 0) : array
    {
        if (!isset($inputMenu[$parentIndex])) {
            return $inputMenu;
        }
        
        if ($inputMenu[$parentIndex]['IS_PARENT']) {
            $parentDepth = $inputMenu[$parentIndex]['DEPTH_LEVEL'];
            $index       = $parentIndex + 1;
            $childMenu   = [];
            
            while (isset($inputMenu[$index]) && $inputMenu[$index]['DEPTH_LEVEL'] > $parentDepth) {
                $childMenu[] = $inputMenu[$index];
                unset($inputMenu[$index]);
                $index++;
            }
            unset($index);
            
            $childMenu                           = self::makeTree($childMenu);
            $inputMenu[$parentIndex]['CHILDREN'] = $childMenu;
            unset($childMenu);
        }
        
        $inputMenu = self::resetMenuIndexes($inputMenu);
        
        return self::makeTree($inputMenu, $parentIndex + 1);
    }
    
    /**
     *  Callback that returns default menu markup
     */
    protected function setDefaultMarkupFunction()
    {
        $this->callable[0] = function ($menu = [], $title = '') {
            if (empty($menu)) {
                return '';
            }
            
            $outString = '<ul>';
            foreach ($menu as $index => $item) {
                
                $class = [];
                if (!isset($arResult[$index - 1])) {
                    $class[] = 'first';
                } elseif (!isset($arResult[$index + 1])) {
                    $class[] = 'last';
                }
                
                if ($item['SELECTED']) {
                    $class[] = 'selected';
                }
                
                $outString .= '<li';
                if (!empty($class)) {
                    $outString .= ' class="' . implode($class, ' ') . '""';
                }
                $outString .= '>';
                $outString .= '<a href="' . $item['LINK'] . '"';
                // if (!empty($class)) {
                //     $outString .= ' class="' . implode($class, ' ') . '""';
                // }
                $outString .= '>';
                $outString .= $item['TEXT'];
                $outString .= '</a>';
                $outString .= $this->drawMenuNextLevel($item['CHILDREN'], $item['DEPTH_LEVEL'] + 1, $item['TEXT']);
                $outString .= '</li>';
            }
            $outString .= '</ul>';
            
            return $outString;
        };
    }
    
    /**
     * @param callable $func
     * @param int      $level
     *
     * @return $this
     */
    public function setMarkupFunction(callable $func, $level = 0) : TreeMenuBuilder
    {
        if ((int)$level > 0) {
            $this->callable[$level] = $func;
        } else {
            $this->callable[] = $func;
        }
        
        return $this;
    }
    
    /**
     * Prints Menu
     *
     * @return $this
     */
    public function drawMenu() : TreeMenuBuilder
    {
        echo $this->drawMenuNextLevel($this->menu);
        
        return $this;
    }
    
    /**
     * @param array  $menu
     * @param int    $depth
     * @param string $title
     *
     * @return string
     */
    public function drawMenuNextLevel(array $menu = [], int $depth = 1, string $title = '') : string
    {
        if ($depth > $this->maxDepth || $depth < 1) {
            return '';
        }
        
        if (isset($this->callable[$depth])) {
            return $this->callable[$depth]($menu, $title);
        }
        
        return $this->callable[0]($menu, $title);
    }
}
