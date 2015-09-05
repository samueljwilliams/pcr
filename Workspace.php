<?php
/**
 * 
 * The Workspace object represents a "view" of an actual repository workspace entity as seen through the authorization settings of its associated Session.
 * Each Workspace object is associated one-to-one with a Session object.
 * The Workspace object can be acquired by calling Session->getWorkspace() on the associated Session object.
 * @author 
 * @version 1.0
 * 
 */
class Workspace {
	private $pm;
	private $workspace;
	
	/**
	 * 
	 * Creates a new Workspace object, given an active session (i.e. PersistenceManager).
	 * @param generic $pm the active PersistenceManager.
	 * 
	 */
	public function __construct(&$pm) {
		$this->pm =& $pm;

		$this->workspace = $pm->getWorkspace();
	}

	/**
	 * Clones the subtree at the node $sourcePath in $workspace to the new location at $destinationPath in this workspace.
	 * If successful, the change is persisted immediately.
	 * Strictly speaking, the $destinationPath parameter is actually an absolute path to the parent node of the new location, appended with the new name desired for the cloned node.
	 * This method cannot be used to clone just an individual property by itself. It clones an entire node and its subtree (including, of course, any properties contained therein).
	 * A NoSuchWorkspaceException is thrown if $workspace does not exist or if the current Session does not have permission to access it.
	 * A PathNotFoundException is thrown if the node at $sourcePath in $workspace or the parent of $destinationPath in this workspace does not exist.
	 * An ItemExistsException is thrown if a node or property already exists at $destinationPath.
	 * An ItemExistException is thrown if a node already exists at $destinationPath.
	 *
	 * @param string $workspace - The name of the workspace from which the node is to be copied.
	 * @param string $sourcePath - the path of the node to be cloned in $workspace.
	 * @param string $destinationPath - the location to which the node at $workspace=>$sourcePath is to be cloned in this workspace.
	 */
	public function _clone($workspace, $sourcePath, $destinationPath) {
		if (!file_exists($_SERVER['PCR'] . "/config/$workspace.xml")) {
			throw new NoSuchWorkspaceException($workspace);
		}

		$config = simplexml_load_file($_SERVER['PCR'] . "/config/$workspace.xml");
		$persistenceManager = (string) $config->persistenceManager;
		
		if (!file_exists($_SERVER['PCR'] . "/PMs/$persistenceManager.php")) {
			throw new RepositoryException("persistence manager does not exist for workspace: $workspace=>$persistenceManager");
		}

		require_once $_SERVER['PCR'] . "/PMs/$persistenceManager.php";
		
		$pm = new $persistenceManager($this->pm->getCredentials(), $workspace, $config);
		
		if (!$pm->isLive()) {
			throw new LoginException("workspace=>$workspace, persistenceManager=>$persistenceManager, userID=>" . $credentials->getUserID());
		}
		
		if ($sourcePath == 'pcr:root') {
			throw new RepositoryException("cannot clone reserved node: $this->workspace=>pcr:root");
		} else if ($pm->getNode($sourcePath) === null) {
			throw new PathNotFoundException("$workspace=>$sourcePath");
		} else if ($this->pm->getNode($destinationPath) !== null) {
			throw new ItemExistsException("$this->workspace=>$destinationPath");
		}
		
		$parent = substr($destinationPath, 0, strrpos($destinationPath, '/'));
		
		if ($parent != 'pcr:root') {
			if ($this->pm->getNode($parent) === null) {
				throw new PathNotFoundException("$this->workspace=>$parent");
			}
		}

		Log4PCR::info("Cloned node (and its descendants, if any): $workspace=>$sourcePath to $this->workspace=>$destinationPath");
		
		$this->pm->_clone($workspace, $sourcePath, $destinationPath);
	}
	
	/**
	 * 
	 * This method copies the node at $sourcePath to the new location at $destinationPath.
	 * If successful, the change is persisted immediately.
	 * Strictly speaking, the $destinationPath parameter is actually an absolute path to the parent node of the new location, appended with the new name desired for the copied node.
	 * This method cannot be used to copy just an individual property by itself.
	 * It copies an entire node and its subtree (including, of course, any properties contained therein).
	 * A PathNotFoundException is thrown if the node at $sourcePath or the parent of $destinationPath does not exist.
	 * An ItemExistException is thrown if a Node already exists at $destinationPath.
	 * @param string $sourcePath the root of the subtree to be copied.
	 * @param string $destinationPath the location to which the subtree is to be copied.
	 * 
	 */
	public function copy($sourcePath, $destinationPath) {
		if ($sourcePath == 'pcr:root') {
			throw new RepositoryException("cannot copy reserved node: $this->workspace=>pcr:root");
		} else if ($this->pm->getNode($sourcePath) === null) {
			throw new PathNotFoundException("$this->workspace=>$sourcePath");
		} else if ($this->pm->getNode($destinationPath) !== null) {
			throw new ItemExistsException("$this->workspace=>$destinationPath");
		}
		
		$parent = substr($destinationPath, 0, strrpos($destinationPath, '/'));
		
		if ($parent != 'pcr:root') {
			if ($this->pm->getNode($parent) === null) {
				throw new PathNotFoundException("$this->workspace=>$parent");
			}
		}
					
		Log4PCR::info("Copied node (and its descendants, if any): $this->workspace=>$sourcePath to $this->workspace=>$destinationPath");
		
		$this->pm->copy($sourcePath, $destinationPath);
	}
	
	/**
	 * 
	 * Returns an string array containing the names of all workspaces in the repository.
	 * In order to access one of the listed workspaces, the user performs another Repository->login, specifying the name of the desired workspace, and receives a new Session object.
	 * @return array string array of names of accessible workspaces.
	 * 
	 */
	public function getAccessibleWorkspaceNames() {
		$accessibleWorkspaceNames = array();
		
		if ($handle = opendir($_SERVER['PCR'] . '/config')) {
			while (false !== ($workspace = readdir($handle))) {
				if ($workspace != '.' && $workspace != '..') {
					$accessibleWorkspaceNames[] = substr($workspace, 0, strrpos($workspace, '.'));
				}
			}
						
			closedir($handle);
		}
		
		return $accessibleWorkspaceNames;
	}
	
	/**
	 * 
	 * Returns the name of the actual persistent workspace represented by this Workspace object.
	 * @return string the name of this workspace.
	 * 
	 */
	public function getName() {
		return $this->workspace;
	}

	/**
	 * 
	 * Gets the QueryManager.
	 * Returns the QueryManager object, through search methods are accessed.
	 * @return QueryManager the QueryManager object.
	 * 
	 */
	public function getQueryManager() {
		return new QueryManager($this->pm);
	}
}

?>