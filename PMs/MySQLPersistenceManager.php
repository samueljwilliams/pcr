<?php

class MySQLPersistenceManager implements PersistenceManager {
	private $config = null;
	private $credentials = null;
	private $isLive = false;
	private $link = null;
	private $workspace = null;
	
	public function __construct (Credentials $credentials, $workspace, $config) {
		$link = @mysql_connect($config->server, $credentials->getUserID(), $credentials->getPassword(), 1);
		
		if (mysql_stat($link) !== null) {
			if (!mysql_select_db($workspace, $link)) {
				$sql = "CREATE DATABASE $workspace";
				
				if (mysql_query($sql, $link)) {
					mysql_select_db($workspace, $link);
					
					$sql = "CREATE TABLE c (p text NOT NULL,
								n text NOT NULL,
								v text NOT NULL,
								KEY INDEX1 (p (1000)),
								KEY INDEX2 (p (850), n (150)),
								KEY INDEX3 (p (550), n (150), v (300)),
								KEY INDEX4 (v (1000)))
								ENGINE = MyISAM DEFAULT CHARSET = latin1";
					
					mysql_query($sql, $link);
				} else {
					throw new RepositoryException("in MySQL, cannot create workspace: $workspace");
				}
			}
			
			$this->credentials = $credentials;
			$this->workspace = $workspace;
			$this->config = $config;
			$this->link = $link;
			$this->isLive = true;
		}
	}
		
	public function _clone($workspace, $sourcePath, $destinationPath) {
		$link = @mysql_connect($this->config->server, $this->credentials->getUserID(), $this->credentials->getPassword(), 1);
		
		mysql_select_db($workspace, $link);
		
		$sql = "SELECT p, n, v
				FROM c
				WHERE p = '$sourcePath' OR
					p LIKE '$sourcePath/%'";
					
		$result = mysql_query($sql, $link);
		
		$i = 0;
		$clone = '';
		
		if (mysql_num_rows($result) >= 1) {
			while($row = mysql_fetch_row($result)) {
				if ($i > 0) {
					$clone .= ', ';
				}
				
				$path = $destinationPath . substr($row[0], strlen($sourcePath));
				
				$clone .= "('$path','$row[1]','$row[2]')";
				
				$i++;
			}
		}
		
		$sql = "INSERT INTO c (p, n, v) VALUES $clone";
		
		mysql_query($sql, $this->link);
	}
	
	public function addNode($path) {
		$sql = "INSERT INTO c (p, n, v)
					VALUES ('$path', 'pcr:created', 'temporary')";
					
		mysql_query($sql, $this->link);
	}

	public function copy($sourcePath, $destinationPath) {
		$sql = "INSERT INTO c (p, n, v)
					SELECT
						CONCAT('$destinationPath', SUBSTRING(c2.p, LENGTH('$sourcePath') + 1)),
						c2.n,
						c2.v
					FROM c c2
					WHERE c2.p = '$sourcePath' OR
						c2.p LIKE '$sourcePath/%'";
						
		mysql_query($sql, $this->link);
	}
	
	public function getCredentials() {
		return $this->credentials;
	}
	
	public function getNode($path) {
		$sql = "SELECT p
					FROM c
					WHERE p = '$path' LIMIT 1";
		
		$result = mysql_query($sql, $this->link);
		
		$return = null;
		
		if (mysql_num_rows($result) == 1) {
			$return = implode(mysql_fetch_row($result));
		}
			
		mysql_free_result($result);
		
        return $return; // string: path or null
	}
	
	public function getNodes($path) {
		$sql = "SELECT p
					FROM c
					WHERE p LIKE '$path/%' AND
						p NOT LIKE '$path/%/%'
					GROUP BY p
					ORDER BY p ASC";
		
		$result = mysql_query($sql, $this->link);
		
		$return = array();
		
		if (mysql_num_rows($result) >= 1) {
			while($row = mysql_fetch_row($result)) {
				$return[] = $row[0];
			}
		}
		
		mysql_free_result($result);
		
		return $return; // array: paths or empty
	}
	
	public function getProperties($path) {
		$sql = "SELECT p, n
					FROM c
					WHERE p = '$path'
					GROUP BY p, n
					ORDER BY n ASC";
		
		$result = mysql_query($sql, $this->link);
		
		$return = array();
			
		if (mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_row($result)) {
				$return[] = array($row[0], $row[1]);
			}
		}
		
		mysql_free_result($result);
				        
        return $return; // array: properties or empty
	}
	
	public function getProperty($path, $name) {
		$sql = "SELECT v
					FROM c
					WHERE p = '$path' AND
						n = '$name'";
		
		$result = mysql_query($sql, $this->link);
		
		$return = null;
			
		if (mysql_num_rows($result) == 1) {
			$return = implode(mysql_fetch_row($result));
		} else if (mysql_num_rows($result) > 1) {
			while($row = mysql_fetch_row($result)) {
				$return[] = $row[0];
			}
		}
			
		mysql_free_result($result);
									
		return $return; // string: value or null
	}
	
	public function getReferences($path) {
		$sql = "SELECT p, n
					FROM c
					WHERE v = 'pcr:reference=>$path'
					GROUP BY p, n
					ORDER BY p, n ASC";
		
		$result = mysql_query($sql, $this->link);
		
		$return = array();
			
		if (mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_row($result)) {
				$return[] = array($row[0], $row[1]);
			}
		}
		
		mysql_free_result($result);
				        
        return $return; // array: properties or empty
	}
	
	public function getWorkspace() {
		return $this->workspace;
	}
	
	public function hasNodes($path) {
		$sql = "SELECT p
					FROM c
					WHERE p LIKE '$path/%' AND
						p NOT LIKE '$path/%/%'
					LIMIT 1";
		
		$result = mysql_query($sql, $this->link);
		
		if (mysql_num_rows($result) > 0) {
			$return = true;
		} else {
			$return = false;
		}
				
		mysql_free_result($result);
		
		return $return; // array: paths or empty
	}
	
	public function hasProperties($path) {
		$sql = "SELECT p
					FROM c
					WHERE p = '$path'
					LIMIT 1";
		
		$result = mysql_query($sql, $this->link);
		
		if (mysql_num_rows($result) > 0) {
			$return = true;
		} else {
			$return = false;
		}
					
		mysql_free_result($result);
				        
        return $return; // array: properties or empty
	}
	
	public function hasReferences($path) {
		$sql = "SELECT p
					FROM c
					WHERE v = 'pcr:reference=>$path'
					LIMIT 1";
		
		$result = mysql_query($sql, $this->link);
		
		if (mysql_num_rows($result) > 0) {
			$return = true;
		} else {
			$return = false;
		}
			
		mysql_free_result($result);
				        
        return $return; // array: properties or empty
	}
	
	public function isLive() {
		return $this->isLive;
	}
	
	public function move($sourcePath, $destinationPath) {
		$sql = "UPDATE c
					SET p = CONCAT('$destinationPath', SUBSTRING(p, LENGTH('$sourcePath') + 1))
					WHERE p = '$sourcePath' OR
						p LIKE '$sourcePath/%'";
						
		mysql_query($sql, $this->link);
		
		$sql = "UPDATE c
					SET v = CONCAT('pcr:reference=>', '$destinationPath', SUBSTR(v, length('pcr:reference=>$sourcePath') + 1))
					WHERE v = 'pcr:reference=>$sourcePath' OR
						v LIKE 'pcr:reference=>$sourcePath/%'";
					
		mysql_query($sql, $this->link);
	}
	
	private function processQuery($array) {
		$operator1 = $array[0];
		$name = $array[1];
		if (isset($array[2]))
			$value = $array[2];
		$result = null;
		
		if ($operator1 == 'rf') {
			$result = $this->processReferenceQuery($name, $value);			
		} else {
			if ($operator1 == 'lo')
				$result = " $name ";
			else if ($operator1 == 'eq' || $operator1 == 'lk' || $operator1 == 'ne' || $operator1 == 'nl') {
				switch ($operator1) {
					case 'eq':
						$operator2 = ' = ';
						break;
					case 'lk':
						$operator2 = ' LIKE ';
						break;
					case 'ne':
						$operator2 = ' != ';
						break;
					case 'nl':
						$operator2 = ' NOT LIKE ';
						break;
				}
				
				if ($name == 'pcr:path')
					$result = "(c1.p $operator2 '$value')";
				else if ($name == '*') {
					switch ($operator1) {
						case 'eq':
							$condition = '> 0';
							break;
						case 'lk':
							$condition = '> 0';
							break;
						case 'ne':
							$operator2 = ' = ';
							$condition = '= 0';
							break;
						case 'nl':
							$operator2 = ' LIKE ';
							$condition = '= 0';
							break;
					}
					
					$result = "((SELECT COUNT(c2.p)
									FROM c c2
									WHERE
										c2.p = c1.p AND
										c2.v $operator2 '$value') $condition)";
				} else
					$result = "((SELECT COUNT(c2.p)
									FROM c c2
									WHERE
										c2.p = c1.p AND
										c2.n = '$name' AND
										c2.v $operator2 '$value') > 0)";
										
				$result = str_replace('*', '%', $result);
			} else if ($operator1 == 'ge' || $operator1 == 'gt' || $operator1 == 'le' || $operator1 == 'lt') {
				switch ($operator1) {
					case 'ge':
						$operator2 = ' >= ';
						break;
					case 'gt':
						$operator2 = ' > ';
						break;
					case 'le':
						$operator2 = ' <= ';
						break;
					case 'lt':
						$operator2 = ' < ';
						break;
				}
				
				$result = "((SELECT COUNT(c2.p)
								FROM c c2
								WHERE
									c2.p = c1.p AND
									c2.n = '$name' AND
									c2.v $operator2 $value) > 0)";
			}
		}
		
		return $result;
	}
	
	private function processReferenceQuery($name1, $value1) {
		$operator1 = $value1[0];
		$name2 = $value1[1];
		$value2 = $value1[2];
		
		if ($operator1 == 'eq' || $operator1 == 'lk' || $operator1 == 'ne' || $operator1 == 'nl') {
			switch ($operator1) {
				case 'eq':
					$operator2 = ' = ';
					break;
				case 'lk':
					$operator2 = ' LIKE ';
					break;
				case 'ne':
					$operator2 = ' != ';
					break;
				case 'nl':
					$operator2 = ' NOT LIKE ';
					break;
			}
			
			if ($name2 == '*') {
				switch ($operator1) {
					case 'eq':
						$condition = '> 0';
						break;
					case 'lk':
						$condition = '> 0';
						break;
					case 'ne':
						$operator2 = ' = ';
						$condition = '= 0';
						break;
					case 'nl':
						$operator2 = ' LIKE ';
						$condition = '= 0';
						break;
				}
				
				$result = "((SELECT COUNT(c2.p)
								FROM c c2
								WHERE
									c2.p IN (SELECT SUBSTR(c3.v, 16)
												FROM c c3
												WHERE
													c3.p = c1.p AND
													c3.n = '$name1' AND
													SUBSTR(c3.v, 1, 15) = 'pcr:reference=>') AND
									c2.v $operator2 '$value2') $condition)";
									
			} else
				$result = "((SELECT COUNT(c2.p)
								FROM c c2
								WHERE
									c2.p IN (SELECT SUBSTR(c3.v, 16)
												FROM c c3
												WHERE
													c3.p = c1.p AND
													c3.n = '$name1' AND
													SUBSTR(c3.v, 1, 15) = 'pcr:reference=>') AND
									c2.n = '$name2' AND
									c2.v $operator2 '$value2') > 0)";
									
			$result = str_replace('*', '%', $result);
		} else if ($operator1 == 'ge' || $operator1 == 'gt' || $operator1 == 'le' || $operator1 == 'lt') {
			switch ($operator1) {
				case 'ge':
					$operator2 = ' >= ';
					break;
				case 'gt':
					$operator2 = ' > ';
					break;
				case 'le':
					$operator2 = ' <= ';
					break;
				case 'lt':
					$operator2 = ' < ';
					break;
			}
			
			$result = "((SELECT COUNT(c2.p)
							FROM c c2
							WHERE
								c2.p IN (SELECT SUBSTR(c3.v, 16)
											FROM c c3
											WHERE
												c3.p = c1.p AND
												c3.n = '$name1' AND
												SUBSTR(c3.v, 1, 15) = 'pcr:reference=>') AND
								c2.n = '$name2' AND
								c2.v $operator2 $value2) > 0)";
		}
			
		return $result;
	}
	
	public function query($statement) {
		$sql = null;
		
		foreach ($statement as $value) {
			$sql .= $this->processQuery($value);
		}
		
		$result = mysql_query("SELECT c1.p FROM c c1 WHERE $sql GROUP BY c1.p ORDER BY c1.p ASC", $this->link);
		
		$return = array();
		
		if (mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_row($result)) {
				$return[] = $row[0];
			}
		}
		
		return $return;
	}
	
	public function removeNode($path) {
		$sql = "DELETE
					FROM c
					WHERE p = '$path' OR
						p LIKE '$path/%'";
		
		mysql_query($sql, $this->link);
	}
	
	public function removeProperty($path, $name) {
		$sql = "DELETE FROM c
					WHERE p = '$path' AND
						n = '$name'";
		
		mysql_query($sql, $this->link);
	}
	
	public function setProperty($path, $name, $value) {
		$sql = "DELETE FROM c
					WHERE p = '$path' AND n = '$name'";
					
		mysql_query($sql, $this->link);
		
		$sql = null;
		
		if (is_array($value)) {
			$array = $value;
			$counter = 0;

			foreach ($array as $value) {
				if ($counter > 0)
					$sql .= ',';
					
				if ($value !== null) {
					$sql .= "('$path', '$name', '$value')";
					
					$counter++;
				}
			}
		} else {
			$sql = "('$path', '$name', '$value')";
		}
			
		$sql = "INSERT INTO c (p, n, v)
        			VALUES $sql";
        				
        mysql_query($sql, $this->link);
	}
}

?>