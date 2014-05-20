<?php

require_once 'ads.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function ads_civicrm_config(&$config) {
  _ads_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function ads_civicrm_xmlMenu(&$files) {
  _ads_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function ads_civicrm_install() {
  return _ads_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function ads_civicrm_uninstall() {
  return _ads_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function ads_civicrm_enable() {
  return _ads_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function ads_civicrm_disable() {
  return _ads_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function ads_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _ads_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function ads_civicrm_managed(&$entities) {
  return _ads_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function ads_civicrm_caseTypes(&$caseTypes) {
  _ads_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function ads_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _ads_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_searchColumns
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_searchColumns
 */
function ads_civicrm_searchColumns($objectName, &$headers, &$rows, &$selector) {
  if ($objectName == 'contribution' && $_GET['q'] == 'civicrm/contact/view/contribution') {
    foreach ($headers as $key => & $value) {
      if (array_key_exists('sort', $value) && $value['sort'] == 'thankyou_date') {
        $value['name'] = ts('Soft Credit Name');
        unset($value['sort']);
      }
      if (array_key_exists('sort', $value) && $value['sort'] == 'product_name') {
        unset($headers[$key]);
      }
    }
    $alterRows = array();
    foreach ($rows as $value) {
      $alterRows[$value['contribution_id']] = $value;
    }
    $alterRows;
    if (!empty($alterRows)) {
      $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=", FALSE, NULL, FALSE);
      $query = "SELECT 
IF(COUNT(ccs.id) = 1 AND contribution.total_amount = ccs.amount, 
CONCAT('<a href={$url}',ccs.contact_id, '>', contact.display_name, '</a>'),
GROUP_CONCAT(
CONCAT(CONCAT('<a href=" . '"' . "{$url}',ccs.contact_id, '". '"'. ">', contact.display_name, '</a>'), ' (', symbol, CAST(ccs.amount AS CHAR), ')' )
      ORDER BY contact.sort_name SEPARATOR ', '
    )
  ) soft_credit_name,
contribution_id  FROM civicrm_contribution_soft ccs
INNER JOIN civicrm_contribution contribution ON contribution.id = ccs.contribution_id
INNER JOIN civicrm_contact contact ON contact.id = ccs.contact_id
LEFT JOIN civicrm_currency cur ON cur.name = ccs.currency
WHERE contribution_id IN (" . implode (', ', array_keys($alterRows)) . ')
GROUP BY contribution.id;';
      $dao = CRM_Core_DAO::executeQuery($query);
      while ($dao->fetch()) {
        $alterRows[$dao->contribution_id]['soft_credit_name'] = $dao->soft_credit_name;
      }
    }
    $rows = array_merge($alterRows, array());
  }
}
