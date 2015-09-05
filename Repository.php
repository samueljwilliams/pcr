<?php
/**
 * 
 * Repository class represents the entry point into the content repository.
 * @author 
 * @version 1.0
 * 
 */
class Repository {
	/**
	 * 
	 * Authenticates the user using the supplied credentials.
	 * If workspace is recognized as the name of an existing workspace in the repository and authorization to access that workspace is granted, then a new Session object is returned.
	 * If authentication or authorization for the specified workspace fails, a LoginException is thrown.
	 * If workspace is not recognized, a NoSuchWorkspaceException is thrown.
	 * @param Credentials $credentials the credentials of the user.
	 * @param string $workspace the name of the workspace.
	 * @return Session a valid session for the user to access the repository.
	 *  
	 */
	public static function login(Credentials $credentials, $workspace) {
		if (!file_exists($_SERVER['PCR'] . "/config/$workspace.xml")) {
			throw new NoSuchWorkspaceException($workspace);
		}

		$config = simplexml_load_file($_SERVER['PCR'] . "/config/$workspace.xml");
		$persistenceManager = (string) $config->persistenceManager;
		
		if (!file_exists($_SERVER['PCR'] . "/PMs/$persistenceManager.php")) {
			throw new RepositoryException("persistence manager does not exist for workspace: $workspace=>$persistenceManager");
		}

		require_once $_SERVER['PCR'] . "/PMs/$persistenceManager.php";
		
		$pm = new $persistenceManager($credentials, $workspace, $config);
		
		if (!$pm->isLive()) {
			throw new LoginException("workspace=>$workspace, persistenceManager=>$persistenceManager, userID=>" . $credentials->getUserID());
		}

		Log4PCR::access("workspace=>$workspace, persistenceManager=>$persistenceManager, userID=>" . $credentials->getUserID());
		
		return new Session($pm);
	}
}

?>