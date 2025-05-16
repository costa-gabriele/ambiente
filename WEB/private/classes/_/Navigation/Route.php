<?php namespace _\Navigation;

class Route {
	
	private static $routes = [
		'notFound' => null, # Not found page
		'exactURIs' => [], # Exact URIs
		'patterns' => [] # Patterns
	];
	
	const DESTINATION_KEYS = [
		'type',
		'path',
		'label'
	];
	
	const FILE_TYPE = RouteType::FILE->value;
	const PAGE_TYPE = RouteType::PAGE->value;
	const WS_TYPE = RouteType::WS->value;

	private static function load(): bool {
		
		if(file_exists(ROUTES_CACHE_PATH)) {
			try {
				$existingRoutes = unserialize(file_get_contents(ROUTES_CACHE_PATH));
			} catch(\Exception $e) {
				return false;
			}
		} else {
			$existingRoutes =  [];
		}
		
		self::$routes['notFound'] = (empty(self::$routes['notFound'])) ? $existingRoutes['notFound'] : self::$routes['notFound'];
		self::$routes['exactURIs'] = array_merge(($existingRoutes['exactURIs'] ?? []), self::$routes['exactURIs']);
		self::$routes['patterns'] = array_merge(($existingRoutes['patterns'] ?? []), self::$routes['patterns']);
		
		return true;
		
	}
	
	public static function resolve(string $pURI): array {
		
		$uri = $pURI;

		$destination = [
			'info' => null,
			'input' => []
		];
		
		if(self::load()) {
			
			if(!empty(self::$routes['exactURIs'][$uri]))
				$destination['info'] = self::$routes['exactURIs'][$uri];
			else {
			
				foreach(self::$routes['patterns'] as $routeURI => $destinationInfo) {
					if(@preg_match_all($routeURI, $uri, $input)) {
						$destinationInfo['path'] = preg_replace($routeURI, $destinationInfo['path'], $uri);
						$destination['info'] = $destinationInfo;
						$destination['input'] = $input;
						break;
					}
				}
			
			}

		}

		return $destination;
		
	}
	
	public static function save(): bool {
		
		self::load();
		$routes = serialize(self::$routes);
		return file_put_contents(ROUTES_CACHE_PATH, $routes, LOCK_EX);
		
	}
	
	# Getters and setters
	
	public static function getRoutes(bool $pfUpdate = true): array {

		if($pfUpdate)
			self::load();

		return self::$routes;

	}
	
	public static function getNotFound(): ?string {
		return self::$routes['notFound'];
	}

	public static function add(array $pRequestURIs, array $pDestinationInfo, RouteCategory $pCategory = RouteCategory::EXACT, $pfOverwrite = true): bool {
		
		# destinationInfo validation
		
		$destinationInfo = array_filter (
			$pDestinationInfo,
			function($v, $k) {
				# $v isn't currently used but could be useful to validate further
				return in_array($k, self::DESTINATION_KEYS);
			},
			ARRAY_FILTER_USE_BOTH
		);
		
		switch($pCategory) {
			case RouteCategory::EXACT:
				$arr = &self::$routes['exactURIs'];
				$pDestinationInfo['path'] = realpath($pDestinationInfo['path']);
				break;
			case RouteCategory::PATTERN:
				$arr = &self::$routes['patterns'];
				break;
			default:
				return false;
		}

		if(empty($pDestinationInfo['path']))
			return false;

		foreach($pRequestURIs as $requestURI) {
			if(empty($arr[$requestURI]) || $pfOverwrite)
				$arr[$requestURI] = $pDestinationInfo;
			else
				return false;
		}
		
		return true;
		
	}
	
	public static function addFolder(array $pRequestBaseURIs, string $pDirectoryPath, array $pExcludedExtensions = []): bool {
		
		$directoryPath = realpath(str_replace('/', DIR_SEP, $pDirectoryPath)) . DIR_SEP;

		if(!$directoryPath || !is_dir($directoryPath)) {
			return false;
		}

		$files = array_filter (
			scandir($directoryPath),
			function($element) use ($directoryPath, $pExcludedExtensions) {
				$path = $directoryPath . $element;
				$extension = pathinfo($path, PATHINFO_EXTENSION);
				return (!is_dir($path) && !in_array($extension, $pExcludedExtensions));
			}
		);
		
		# A route is set for each file in the directory
		foreach($files as $file) {
			
			$fAdded = self::add (
				array_map(function($requestURI) use($file) {return $requestURI . $file;}, $pRequestBaseURIs),
				[
					'path' => $directoryPath . $file,
					'type' => self::FILE_TYPE
				]
			);

			if(!$fAdded) return false;

		}

		return true;
				
	}

	public static function addPattern(string $pRequestURIPattern, string $pDestinationPath): bool {
				
		$requestURIPattern = '/' . str_replace('/','\/', URI_ROOT . $pRequestURIPattern) . '/';
		$destinationPath = str_replace('\\', '\\\\', $pDestinationPath);

		$destinationInfo = [
			'path' => $destinationPath,
			'type' => null,
			'label' => null
		];
		
		return self::add([$requestURIPattern], $destinationInfo, RouteCategory::PATTERN);
				
	}
	
	public static function addPage(string $pPageName, array $pRequestURIs = []): bool {
		
		$requestURIs =
			(empty($pRequestURIs)) ?
			[PAGE_URI_ROOT . $pPageName . '/'] :
			array_map(function($requestURI) {return PAGE_URI_ROOT . $requestURI;}, $pRequestURIs)
		;
		
		$viewDir = VIEW_DIR . str_replace('/', DIR_SEP, $pPageName) . DIR_SEP;
		
		# A route is set for each file in the view directory
		self::addFolder($requestURIs, $viewDir, [VIEW_EXTENSION]);
		
		# Finally the page route is added
		return self::add (
			$requestURIs,
			[
				'path' => PAGE_DIR . str_replace('/', DIR_SEP, $pPageName) . '.' . PAGE_EXTENSION,
				'type' => self::PAGE_TYPE,
				'label' => $pPageName
			]
		);
				
	}
	
	public static function addWebService(string $pWebServiceName, array $pRequestURIs = []): bool {
		
		$requestURIs =
			(empty($pRequestURIs)) ?
			[WS_URI_ROOT . $pWebServiceName] :
			array_map(function($requestURI) {return WS_URI_ROOT . $requestURI;}, $pRequestURIs)
		;
		
		return self::add (
			$requestURIs,
			[
				'path' => WS_DIR . str_replace('/', DIR_SEP, $pWebServiceName) . '.' . WS_EXTENSION,
				'type' => self::WS_TYPE,
				'label' => $pWebServiceName
			]
		);
		
	}

	public static function setAdminPage(string $pAdminPageName = ADMIN_PAGE_NAME, array $pAdminPageURIs = [ADMIN_PAGE_URI]): bool {
		return self::addPage($pAdminPageName, $pAdminPageURIs);
	}

	public static function setHomePage(string $pHomePageName = HOME_PAGE_NAME, array $pHomePageURIs = [HOME_PAGE_URI, '']): bool {
		return self::addPage($pHomePageName, $pHomePageURIs);
	}
	
	public static function setNotFoundPage(string $pNotFoundPageName = NOT_FOUND_PAGE_NAME): bool {
		self::$routes['notFound'] = realpath(PAGE_DIR . str_replace('/', DIR_SEP, $pNotFoundPageName) . '.' .  PAGE_EXTENSION);
		return self::addPage($pNotFoundPageName);
	}	
	
}

?>
