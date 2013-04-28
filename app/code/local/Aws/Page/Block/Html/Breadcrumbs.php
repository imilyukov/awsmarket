<?php
/**
 * Created by JetBrains PhpStorm.
 * User: imilyukov
 * Date: 4/28/13
 * Time: 8:26 PM
 * To change this template use File | Settings | File Templates.
 */

class Aws_Page_Block_Html_Breadcrumbs extends Mage_Page_Block_Html_Breadcrumbs
{
    public function clearCrumbs()
    {
        $this->_crumbs = array();

        return $this;
    }
}