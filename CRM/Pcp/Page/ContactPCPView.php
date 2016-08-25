<?php

require_once 'CRM/Core/Page.php';

class CRM_Pcp_Page_ContactPCPView extends CRM_Core_Page {
  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('ContactPCPView'));

    // Example: Assign a variable for use in a template
    $this->assign('currentTime', date('Y-m-d H:i:s'));
	
	$this->getUserPCP();
    parent::run();
  }
  
  public function executePCPSelectQuery() {
	 $contactId = $_GET['cid'];
	$query = "SELECT id, contact_id , status_id, title, is_active, page_type, page_id, goal_amount
		FROM civicrm_pcp
		WHERE contact_id = " . $contactId . " ORDER BY status_id";
	return CRM_Core_DAO::executeQuery($query, array());
  }
  
  public function getPCPClass($pcp) {
	  $approvedId = CRM_Core_OptionGroup::getValue('pcp_status', 'Approved', 'name');
	  $class = '';
      if (/*$pcp->status_id != $approvedId ||*/ $pcp->is_active != 1) {///fix this
        $class = 'disabled';
      }
	  return $class;
  }

  
  public function getPCPTitle($pages, $pcp) {
	 $title = '';
    if ($pages[$pcp->page_type][$pcp->page_id]['title'] == '' || $pages[$pcp->page_type][$pcp->page_id]['title'] == NULL) {
        $title = '(no title found for ' . $pcp->page_type . ' id ' . $pcp->page_id . ')';
      } else $title = $pages[$pcp->page_type][$pcp->page_id]['title'];
	 return $title;
  }
 
  public function getPageURL($pcp) {
	$pageUrl = '';
	if ($pcp->page_type == 'contribute') {
		$pageUrl = CRM_Utils_System::url('civicrm/' . $pcp->page_type . '/transact', 'reset=1&id=' . $pcp->page_id);
	}
	else $pageUrl = CRM_Utils_System::url('civicrm/' . $pcp->page_type . '/register', 'reset=1&id=' . $pcp->page_id);
	return $pageUrl;  
  }
  
  public function getConPages(&$pages) {
	$query = "SELECT id, title, start_date, end_date FROM civicrm_contribution_page WHERE (1)";
    $cpages = CRM_Core_DAO::executeQuery($query);
    while ($cpages->fetch()) {
      $pages['contribute'][$cpages->id]['id'] = $cpages->id;
      $pages['contribute'][$cpages->id]['title'] = $cpages->title;
      $pages['contribute'][$cpages->id]['start_date'] = $cpages->start_date;
      $pages['contribute'][$cpages->id]['end_date'] = $cpages->end_date;
    }
	  
  }
  
  public function getContributions($pcp) {
	$cons = array();
	$query = "SELECT id, total_amount
		FROM civicrm_contribution
		WHERE campaign_id = " . $pcp->page_id;
	$c = CRM_Core_DAO::executeQuery($query);
	while ($c->fetch()) {
		$cons[$c->id]['amount'] = $c->total_amount;
	}
	return $cons;
  }
  
  public function getTotalAmmount($pcp) {
	$total = 0;
	foreach($this->getContributions($pcp) as $con) {
		$total += $con['amount'] ; 
	}
	return $total;
  }
  
  public function getTotalCons($pcp) {
	  return count($this->getContributions($pcp));
  }
  
  public function getEventPages(&$pages) {
	 $query = "SELECT id, title, start_date, end_date, registration_start_date, registration_end_date
                  FROM civicrm_event
                  WHERE is_template IS NULL OR is_template != 1";
    $epages = CRM_Core_DAO::executeQuery($query);
    while ($epages->fetch()) {
      $pages['event'][$epages->id]['id'] = $epages->id;
      $pages['event'][$epages->id]['title'] = $epages->title;
      $pages['event'][$epages->id]['start_date'] = $epages->registration_start_date;
      $pages['event'][$epages->id]['end_date'] = $epages->registration_end_date;
    }  
  }
  public function getUserPCP($action = NULL) {
	
	$pcpSummary = array();
	$pcp = $this->executePCPSelectQuery();
    $status = CRM_PCP_BAO_PCP::buildOptions('status_id', 'create');
	
	

	$pages = array();
	$this->getConPages($pages);
    $this->getEventPages($pages);
	
	
    while ($pcp->fetch()) {
   
		$contact = CRM_Contact_BAO_Contact::getDisplayAndImage($pcp->contact_id);
		
		$class = $this->getPCPClass($pcp);

		$title = $this->getPCPTitle($pages, $pcp);  
		$pageUrl = $this->getPageURL($pcp);
		$editUrl = CRM_Utils_System::url("civicrm/pcp/info?action=update&reset=1&id=" . $pcp->page_id ."&context=dashboard"); 

/*
title (with a link to the page), <
status,  <
contribution page or event <
# of contributions
amount raised
target amount <
link to edit page form.
*/

      $pcpSummary[$pcp->id] = array(
        'id' => $pcp->id,
        'supporter' => $contact['0'],
        'supporter_id' => $pcp->contact_id,
		'goal_amount' => $pcp->goal_amount,
        'status_id' => $status[$pcp->status_id],
        'page_id' => $pcp->page_id,
       	'page_title' => $title,
       	'page_url' => $pageUrl,
       	'page_type' => $pcp->page_type,
		'total_cons' => $this->getTotalCons($pcp),
		'cons_amount' => $this->getTotalAmmount($pcp),
		'edit_url' => $editUrl,
        'title' => $pcp->title,
        'class' => $class,
      );
    }

   // $this->search();
   // $this->pagerAToZ($this->get('whereClause'), $params);

    $this->assign('rows', $pcpSummary);
  }
}
