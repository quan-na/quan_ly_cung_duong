<?php

class MenuController {
    protected $app;

    public function __construct(\Slim\Slim $app) {
        $this->app = $app;
    }

    public function menuList() {
        $statement = $this->app->db->select()->from('menu_item')
                          ->where('ar_owner', '=', $_SESSION['username'])
                          ->where('ar_user', '>', 0, "OR")
                          ->where('ar_group_level', '>=', $_SESSION['group_level'])
                          ->where('ar_group', '>', 0, "OR")
                          ->where('ar_other', '>', 0)
                          ->orderBy('priority','ASC');
        $pdoStatement = $statement->execute();
        $menusArray = $pdoStatement->fetchAll(PDO::FETCH_OBJ);
        // put them to sorted dropdowns
        // ASSUMED there 're only 2 level of menu
        // ASSUMED submenu has lower privilege than its parent
        $sortedArray = array();
        foreach ($menusArray as $menuObj) {
            if (empty($menuObj->parent_menu)) {
                $sortedArray[$menuObj->menu_id] = $menuObj;
                $menuObj->subMenus = array();
            } else {
                $sortedArray[$menuObj->parent_menu]->subMenus[$menuObj->menu_id] = $menuObj;
            }
        }
        $this->app->response()->status(200);
        $this->app->response()->body(json_encode($sortedArray));
    }
}
?>
