<?php
/*
 * Built 26 Jan 2011
 * By E.E. Gilbert
 */
include_once($serverRoot.'/config/dbconnection.php');

class SpecEditReviewManager {

	private $conn;
	private $collId;
	private $collAcronym;

	function __construct(){
		$this->conn = MySQLiConnectionFactory::getCon("write");
	}

	function __destruct(){
 		if(!($this->conn === false)) $this->conn->close();
	}
	
	public function setCollId($id){
		if($id){
			$this->collId = $id;
			$sql = 'SELECT collectionname, institutioncode, collectioncode FROM omcollections WHERE collid = '.$id;
			$rs = $this->conn->query($sql);
			while($r = $rs->fetch_object()){
				$collName = $r->collectionname.' (';
				$this->collAcronym = $r->institutioncode;
				$collName .= $r->institutioncode;
				if($r->collectioncode){
					$collName .= ':'.$r->collectioncode;
					$this->collAcronym .= ':'.$r->collectioncode;
				}
				$collName .= ')';
			}
			$rs->close();
		}
		return $collName;
	}

	public function getEditArr($aStatus, $rStatus){
		if(!$this->collId) return;
		$retArr = Array();
		$sql = 'SELECT e.ocedid,e.occid,e.fieldname,e.fieldvaluenew,e.fieldvalueold,e.reviewstatus,e.appliedstatus,CONCAT_WS(" ",u.firstname,u.lastname) AS username '.
			'FROM omoccuredits e INNER JOIN omoccurrences o ON e.occid = o.occid '.
			'INNER JOIN users u ON e.uid = u.uid '.
			'WHERE o.collid = '.$this->collId;
		if($aStatus === 0 || $aStatus === 1) $sql .= ' AND e.appliedstatus = '.$aStatus.' ';
		if($rStatus){
			if($rStatus == '1-2'){
				$sql .= ' AND e.reviewstatus IN(1,2) ';
			}
			else{
				$sql .= ' AND e.reviewstatus = '.$rStatus.' ';
			}
		}
		$sql .= ' ORDER BY e.fieldname ASC, e.initialtimestamp DESC';
		//echo '<div>'.$sql.'</div>';
		$rs = $this->conn->query($sql);
		while($r = $rs->fetch_object()){
			$ocedid = $r->ocedid;
			$occId = $r->occid;
			$retArr[$occId][$ocedid]['fname'] = $r->fieldname;
			$retArr[$occId][$ocedid]['fvalueold'] = $r->fieldvalueold;
			$retArr[$occId][$ocedid]['fvaluenew'] = $r->fieldvaluenew;
			$retArr[$occId][$ocedid]['rstatus'] = $r->reviewstatus;
			$retArr[$occId][$ocedid]['astatus'] = $r->appliedstatus;
			$retArr[$occId][$ocedid]['uname'] = $r->username;
		}
		$rs->close();
		return $retArr;
	}
	
	public function applyAction($reqArr){
		if(!array_key_exists('ocedid',$reqArr)) return;
		$statusStr = 'SUCCESS: ';
		$ocedidArr = $reqArr['ocedid'];
		$applyTask = $reqArr['applytask'];
		if($ocedidArr){
			if($applyTask == 'apply'){
				//Apply edits with applied status = 0
				$sql = 'SELECT occid, fieldname, fieldvaluenew '.
					'FROM omoccuredits WHERE appliedstatus = 0 AND ocedid IN('.implode(',',$ocedidArr).')';
				$rs = $this->conn->query($sql);
				$eCnt=0;$oCnt=0;$lastOccid = 0;
				while($r = $rs->fetch_object()){
					$uSql = 'UPDATE omoccurrences SET '.$r->fieldname.' = "'.$r->fieldvaluenew.'" WHERE occid = '.$r->occid;
					//echo '<div>'.$uSql.'</div>';
					$this->conn->query($uSql);
					$eCnt++;
					if($r->occid != $lastOccid) $oCnt++;
				}
				$rs->close();
				$statusStr .= $eCnt.' edits applied to '.$oCnt.' specimen records';
			}
			else{
				//Revert edits with applied status = 1
				$sql = 'SELECT occid, fieldname, fieldvalueold '.
					'FROM omoccuredits WHERE appliedstatus = 1 AND ocedid IN('.implode(',',$ocedidArr).')';
				$rs = $this->conn->query($sql);
				$oCnt=0;$lastOccid = 0;
				while($r = $rs->fetch_object()){
					$uSql = 'UPDATE omoccurrences SET '.$r->fieldname.' = "'.$r->fieldvalueold.'" WHERE occid = '.$r->occid;
					//echo '<div>'.$uSql.'</div>';
					$this->conn->query($uSql);
					if($r->occid != $lastOccid) $oCnt++;
				}
				$rs->close();
				$statusStr .= $oCnt.' specimen records reverted to previous values';
			}
			//Change status
			$sql = 'UPDATE omoccuredits SET reviewstatus = '.$reqArr['rstatus'].',appliedstatus = '.($applyTask=='apply'?1:0).' '.
				'WHERE ocedid IN('.implode(',',$ocedidArr).')';
			//echo '<div>'.$sql.'</div>';
			$this->conn->query($sql);
		}
		return $statusStr;
	}

	public function downloadRecords($reqArr){
		if(!array_key_exists('ocedid',$reqArr)) return;
		$ocedidArr = $reqArr['ocedid'];
		//Initiate file
    	$fileName = $this->collAcronym.'SpecimenEdits_'.time().".csv";
    	header ('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header ('Content-Type: text/csv');
		header ("Content-Disposition: attachment; filename=\"$fileName\""); 
		//Get Records
		$sql = 'SELECT e.ocedid,e.occid,e.fieldname,e.fieldvaluenew,e.fieldvalueold,e.reviewstatus,e.appliedstatus,CONCAT_WS(" ",u.firstname,u.lastname) AS username '.
			'FROM omoccuredits e INNER JOIN omoccurrences o ON e.occid = o.occid '.
			'INNER JOIN users u ON e.uid = u.uid '.
			'WHERE o.collid = '.$this->collId.' AND ocedid IN('.implode(',',$ocedidArr).') '.
			'ORDER BY e.fieldname ASC, e.initialtimestamp DESC';
		//echo '<div>'.$sql.'</div>';
		$rs = $this->conn->query($sql);
		if($rs){
			echo "EditId,\"RecordNumber\",\"FieldName\",\"NewValue\",\"OldValue\",\"ReviewStatus\",\"AppliedStatus\",\"UserName\"\n";
			while($r = $rs->fetch_assoc()){
				$reviewStr = '';
				if($r['reviewstatus'] == 1){
					$reviewStr = 'OPEN';
				}
				elseif($r['reviewstatus'] == 2){
					$reviewStr = 'PENDING';
				}
				elseif($r['reviewstatus'] == 3){
					$reviewStr = 'CLOSED';
				}
				echo $r['ocedid'].",".$r['occid'].",\"".$r['fieldname']."\",\"".$r['fieldvaluenew']."\",\"".$r['fieldvalueold']."\",\"".
				$reviewStr."\",\"".($r['appliedstatus']?"APPLIED":"NOT APPLIED")."\",\"".$r['username']."\"\n";
			}
			$rs->close();
		}
		else{
			echo "Recordset is empty.\n";
		}
	}

	public function getCollectionList(){
		global $isAdmin, $userRights;
		$returnArr = Array();
		if($isAdmin || array_key_exists("CollAdmin",$userRights)){
			$sql = 'SELECT DISTINCT c.collid, c.collectionname '.
				'FROM omcollections c '.
				'WHERE colltype LIKE "%specimens%" ';
			if(array_key_exists('CollAdmin',$userRights)){
				$sql .= 'AND c.collid IN('.implode(',',$userRights['CollAdmin']).') '; 
			}
			$sql .= 'ORDER BY c.collectionname';
			//echo $sql;
			$result = $this->conn->query($sql);
			while($row = $result->fetch_object()){
				$returnArr[$row->collid] = $row->collectionname;
			}
			$result->close();
		}
		return $returnArr;
	}

	protected function cleanStr($str){
		$str = str_replace('"','',$str);
		return $str;
	}
}
?>
 