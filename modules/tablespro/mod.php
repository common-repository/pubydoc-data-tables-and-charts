<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TablesProPyt extends ModulePyt {
	public $defaultFont = 'Default';
	public $condParams = null;
	public $fileTypes = array(
		'png', 'jpeg', 'jpg', 'gif', 'ico', 'svg', 'bmp',
		'zip', '7z', 'rar', 'tar',
		'txt', 'pdf', 'html', 'csv', 'xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx'
	);
	public $dbExternalValue = 'External DB';
	public $dbSQLValue = 'SQL Query';
	
	public function init() {
		parent::init();
		DispatcherPyt::addFilter('jsInitVariables', array($this, 'addJsVariables'), 10, 2);
		DispatcherPyt::addAction('tablesEditAddAssets', array($this, 'tablesEditAddAssets'), 10, 1);
		DispatcherPyt::addAction('tablesFrontAddAssets', array($this, 'tablesFrontAddAssets'), 10, 1);
		DispatcherPyt::addAction('tablesIncludeTpl', array($this, 'includeTemplate'), 10, 2);
		DispatcherPyt::addFilter('tableShortcodesList', array($this, 'tableShortcodesList'), 10, 2);
		DispatcherPyt::addFilter('getFontsList', array($this, 'getFontsList'), 10, 2);
		DispatcherPyt::addFilter('addTablesEditSettings', array($this, 'addTablesEditSettings'), 10, 2);
		DispatcherPyt::addFilter('getCellsData', array($this, 'getCellsData'), 10, 2);
		DispatcherPyt::addFilter('getFrontRows', array($this, 'getFrontRows'), 10, 3);
		DispatcherPyt::addFilter('saveNewTable', array($this, 'saveNewTable'), 10, 2);
		DispatcherPyt::addFilter('tableTabsJSLangs', array($this, 'tableTabsJSLangs'), 10, 2);
		DispatcherPyt::addFilter('addShortcodeAttributes', array($this, 'addShortcodeAttributes'), 10, 2);

	}

	public function addJsVariables($jsData) {
		$jsData['fileTypeExts'] = array('png','jpeg','jpg','pdf');
		$jsData['condParams'] = $this->getConditionsParams();
		$jsData['diagramUrl'] = FramePyt::_()->getModule('adminmenu')->getMainLink() . '&tab=diagrams';
		return $jsData;
	}

	public function tablesEditAddAssets( $type ) {
		$path = $this->getModPath() . 'assets/';
		$frame = FramePyt::_();

		$assets = AssetsPyt::_();
		$assets->loadChosenSelects();

		$frame = FramePyt::_();
		$frame->addScript('pyt-tables-pro', $path . 'js/admin.tables.edit.pro.js');
		$frame->addStyle('pyt-tables-pro', $path . 'css/admin.tables.pro.css');

		$frame->addScript('pyt-builder-pro', $path . 'js/admin.builder.pro.js');
		$frame->addStyle('pyt-builder-pro', $path . 'css/admin.builder.pro.css');
		if (empty($type)) {
			$frame->addScript('pyt-builder-toolbar-pro', $path . 'js/admin.builder.toolbar.pro.js');
		}
		$frame->addStyle('pyt-tables-front-pro', $path . 'css/front.tables.pro.css');
		$frame->addScript('pyt-tables-common-pro', $path . 'js/common.tables.pro.js');
	}

	public function tablesFrontAddAssets( $table ) {
		$path = $this->getModPath() . 'assets/';
		$frame = FramePyt::_();
		$options = UtilsPyt::getArrayValue($table, 'options', array(), 2);
		
		if (UtilsPyt::getArrayValue($options, 'lightbox', false, 1)) {
			$frame->addScript('pyt-featherlight', $path . 'lib/featherlight/featherlight.min.js');
			$frame->addStyle('pyt-featherlight', $path . 'lib/featherlight/featherlight.min.css');			
		}
		if (UtilsPyt::getArrayValue($options, 'lightbox', false, 1)) {
			$frame->addScript('pyt-featherlight', $path . 'lib/featherlight/featherlight.min.js');
			$frame->addStyle('pyt-featherlight', $path . 'lib/featherlight/featherlight.min.css');			
		}
		if (UtilsPyt::getArrayValue($options, 'withDiagrams', false, 1)) {
			$frame->getModule('diagrams')->getView()->renderDiagramsAssets();
		}

		$assets = AssetsPyt::_();
		$assets->loadJqueryUi();
		$assets->loadTooltipster();
		$assets->loadDatePicker();

		$frame->addScript('pyt-tables-pro', $path . 'js/common.tables.pro.js');
		$frame->addStyle('pyt-tables-pro', $path . 'css/front.tables.pro.css');
	}

	public function includeTemplate( $tpl, $params = array() ) {
		$this->getView()->includeTemplate($tpl, $params);
	}

	public function tableShortcodesList( $shortcodes, $type ) {
		if ($type == 3) {
			$shortcodes['sql_shortcode'] = array(
				'name' => PYT_SHORTCODE,
				'label' => __('SQL Shortcode', 'publish-your-table'),
				'attrs' => 'sql1=1 sql2="yes"',
				'info' => __('lets build a table using SQL-query with variables. SQL-query you can set on DataBase Tab.', 'publish-your-table'),
			);
		} else {
			/*$shortcodes['history_shortcode'] = array(
				'name' => PYT_SHORTCODE,
				'label' => __('History Shortcode', 'publish-your-table'),
				'attrs' => 'use_history=1',
				'info' => __('lets display an individual table data for each autorized user. Users can change the table data through editable fields on frontend. All user tables can be shown on Table History tab.', 'publish-your-table'),
			);*/
		}		
		return $shortcodes;
	}

	public function addTablesEditSettings( $settings ) {

		return array(
		/*	'roles' => FramePyt::_()->getModule('options')->getAvailableUserRolesSelect(),
			'extensions' => $this->fileTypes,
			'export' => $this->getModel('export')->exportFormats,*/
			'standartFonts' => $this->getFontsList(array(), 'standart')
			);
	}

	public function tableTabsJSLangs( $lang, $tab ) {
		if ('builder' == $tab) {
			$lang['btn-import'] = esc_html__('Import', 'publish-your-table');
			$lang['btn-export'] = esc_html__('Export', 'publish-your-table');
		}
		return $lang;
	}

	public function getConditionsParamsExcel( $forImport = true ) {
		$conditions = $this->getConditionsParams(0);

		$result = array('types' => array(), 'opers' => array());
		if ($forImport) {
			foreach ($conditions['typesExcel'] as $p => $e) {
				$result['types'][$e] = $p;
			}
		} else {
			$result['types'] = $conditions['typesExcel'];
		}
		foreach ($conditions['opers'] as $p => $e) {
			if ($forImport) {
				$result['opers'][$e['excel']] = $p;
			} else {
				$result['opers'][$p] = $e['excel'];
			}
		}
		return $result;
	}

	public function getConditionsParams() {
		if (is_null($this->condParams)) {
			$this->condParams = array(
				'types' => array(
					'cell' => __('Cell is', 'publish-your-table'),
					'text' => __('Text', 'publish-your-table'),
				),
				'typesExcel' => array(
					'cell' => 'cellIs',
					'text' => 'containsText',
				),
				'opers' => array(
					'equals' => array(
						'label' => __('equals', 'publish-your-table'),
						'type' => 'cell',
						'value' => '==',
						'excel' => 'equal',
					),
					'notEquals' => array(
						'label' => __('not equals', 'publish-your-table'),
						'type' => 'cell',
						'value' => '!=',
						'excel' => 'notEqual',
					),
					'greater' => array(
						'label' => __('greater than', 'publish-your-table'),
						'type' => 'cell',
						'value' => '>',
						'excel' => 'greaterThan',
					),
					'greaterOrEquals' => array(
						'label' => __('greater than or equal', 'publish-your-table'),
						'type' => 'cell',
						'value' => '>=',
						'excel' => '',
					),
					'less' => array(
						'label' => __('less than', 'publish-your-table'),
						'type' => 'cell',
						'value' => '<',
						'excel' => 'lessThan',
					),
					'lessOrEquals' => array(
						'label' => __('less than or equal', 'publish-your-table'),
						'type' => 'cell',
						'value' => '<=',
						'excel' => 'lessThanOrEqual',
					),
					'between' => array(
						'label' => __('between', 'publish-your-table'),
						'type' => 'cell',
						'value' => '{1} <= {0} && {0} <= {2}',
						'excel' => 'between',
					),
					'begins' => array(
						'label' => __('begins with', 'publish-your-table'),
						'type' => 'text',
						'value' => '^{0}',
						'excel' => 'beginsWith',
					),
					'ends' => array(
						'label' => __('ends with', 'publish-your-table'),
						'type' => 'text',
						'value' => '{0}$',
						'excel' => 'endsWith',
					),
					'contains' => array(
						'label' => __('contains', 'publish-your-table'),
						'type' => 'text',
						'value' => '',
						'excel' => 'containsText',
					),
					'notContains' => array(
						'label' => __('not contains', 'publish-your-table'),
						'type' => 'text',
						'value' => '',
						'excel' => 'notContains',
					),
				),
			);
		}
		return $this->condParams;
	}

	public function getFontsList( $fonts, $type ) {
		if ($type == 'standart') {
			return array('Georgia','Palatino Linotype','Times New Roman','Arial','Helvetica','Arial Black','Gadget','Calibri', 'Comic Sans MS','Impact','Charcoal','Lucida Sans Unicode','Lucida Grande','Tahoma','Geneva','Trebuchet MS','Verdana','Geneva','Courier New','Courier','Lucida Console','Monaco');
		} else {
			return array('ABeeZee','Abel','Abril Fatface','Aclonica','Acme','Actor','Adamina','Advent Pro','Aguafina Script','Akronim','Aladin','Aldrich','Alef','Alegreya','Alegreya SC','Alegreya Sans','Alegreya Sans SC','Alex Brush','Alfa Slab One','Alice','Alike','Alike Angular','Allan','Allerta','Allerta Stencil','Allura','Almendra','Almendra Display','Almendra SC','Amarante','Amaranth','Amatic SC','Amethysta','Amiri','Anaheim','Andada','Andika','Angkor','Annie Use Your Telescope','Anonymous Pro','Antic','Antic Didone','Antic Slab','Anton','Arapey','Arbutus','Arbutus Slab','Architects Daughter','Archivo Black','Archivo Narrow','Arimo','Arizonia','Armata','Artifika','Arvo','Asap','Asset','Astloch','Asul','Atomic Age','Aubrey','Audiowide','Autour One','Average','Average Sans','Averia Gruesa Libre','Averia Libre','Averia Sans Libre','Averia Serif Libre','Bad Script','Balthazar','Bangers','Basic','Battambang','Baumans','Bayon','Belgrano','Belleza','BenchNine','Bentham','Berkshire Swash','Bevan','Bigelow Rules','Bigshot One','Bilbo','Bilbo Swash Caps','Biryani','Bitter','Black Ops One','Bokor','Bonbon','Boogaloo','Bowlby One','Bowlby One SC','Brawler','Bree Serif','Bubblegum Sans','Bubbler One','Buenard','Butcherman','Butterfly Kids','Cabin','Cabin Condensed','Cabin Sketch','Caesar Dressing','Cagliostro','Calligraffitti','Cambay','Cambo','Candal','Cantarell','Cantata One','Cantora One','Capriola','Cardo','Carme','Carrois Gothic','Carrois Gothic SC','Carter One','Caudex','Cedarville Cursive','Ceviche One','Changa One','Chango','Chau Philomene One','Chela One','Chelsea Market','Chenla','Cherry Cream Soda','Cherry Swash','Chewy','Chicle','Chivo','Cinzel','Cinzel Decorative','Clicker Script','Coda','Codystar','Combo','Comfortaa','Coming Soon','Concert One','Condiment','Content','Contrail One','Convergence','Cookie','Copse','Corben','Courgette','Cousine','Coustard','Covered By Your Grace','Crafty Girls','Creepster','Crete Round','Crimson Text','Croissant One','Crushed','Cuprum','Cutive','Cutive Mono','Damion','Dancing Script','Dangrek','Dawning of a New Day','Days One','Dekko','Delius','Delius Swash Caps','Delius Unicase','Della Respira','Denk One','Devonshire','Dhurjati','Didact Gothic','Diplomata','Diplomata SC','Domine','Donegal One','Doppio One','Dorsa','Dosis','Dr Sugiyama','Droid Sans','Droid Sans Mono','Droid Serif','Duru Sans','Dynalight','EB Garamond','Eagle Lake','Eater','Economica','Ek Mukta','Electrolize','Elsie','Elsie Swash Caps','Emblema One','Emilys Candy','Engagement','Englebert','Enriqueta','Erica One','Esteban','Euphoria Script','Ewert','Exo','Exo 2','Expletus Sans','Fanwood Text','Fascinate','Fascinate Inline','Faster One','Fasthand','Fauna One','Federant','Federo','Felipa','Fenix','Finger Paint','Fira Mono','Fira Sans','Fjalla One','Fjord One','Flamenco','Flavors','Fondamento','Fontdiner Swanky','Forum','Francois One','Freckle Face','Fredericka the Great','Fredoka One','Freehand','Fresca','Frijole','Fruktur','Fugaz One','GFS Didot','GFS Neohellenic','Gabriela','Gafata','Galdeano','Galindo','Gentium Basic','Gentium Book Basic','Geo','Geostar','Geostar Fill','Germania One','Gidugu','Gilda Display','Give You Glory','Glass Antiqua','Glegoo','Gloria Hallelujah','Goblin One','Gochi Hand','Gorditas','Goudy Bookletter 1911','Graduate','Grand Hotel','Gravitas One','Great Vibes','Griffy','Gruppo','Gudea','Gurajada','Habibi','Halant','Hammersmith One','Hanalei','Hanalei Fill','Handlee','Hanuman','Happy Monkey','Headland One','Henny Penny','Herr Von Muellerhoff','Hind','Holtwood One SC','Homemade Apple','Homenaje','IM Fell DW Pica','IM Fell DW Pica SC','IM Fell Double Pica','IM Fell Double Pica SC','IM Fell English','IM Fell English SC','IM Fell French Canon','IM Fell French Canon SC','IM Fell Great Primer','IM Fell Great Primer SC','Iceberg','Iceland','Imprima','Inconsolata','Inder','Indie Flower','Inika','Irish Grover','Istok Web','Italiana','Italianno','Jacques Francois','Jacques Francois Shadow','Jaldi','Jim Nightshade','Jockey One','Jolly Lodger','Josefin Sans','Josefin Slab','Joti One','Judson','Julee','Julius Sans One','Junge','Jura','Just Another Hand','Just Me Again Down Here','Kalam','Kameron','Kantumruy','Karla','Karma','Kaushan Script','Kavoon','Kdam Thmor','Keania One','Kelly Slab','Kenia','Khand','Khmer','Khula','Kite One','Knewave','Kotta One','Koulen','Kranky','Kreon','Kristi','Krona One','Kurale','La Belle Aurore','Laila','Lakki Reddy','Lancelot','Lateef','Lato','League Script','Leckerli One','Ledger','Lekton','Lemon','Libre Baskerville','Life Savers','Lilita One','Lily Script One','Limelight','Linden Hill','Lobster','Lobster Two','Londrina Outline','Londrina Shadow','Londrina Sketch','Londrina Solid','Lora','Love Ya Like A Sister','Loved by the King','Lovers Quarrel','Luckiest Guy','Lusitana','Lustria','Macondo','Macondo Swash Caps','Magra','Maiden Orange','Mako','Mallanna','Mandali','Marcellus','Marcellus SC','Marck Script','Margarine','Marko One','Marmelad','Martel','Martel Sans','Marvel','Mate','Mate SC','Maven Pro','McLaren','Meddon','MedievalSharp','Medula One','Megrim','Meie Script','Merienda','Merienda One','Merriweather','Merriweather Sans','Metal','Metal Mania','Metamorphous','Metrophobic','Michroma','Milonga','Miltonian','Miltonian Tattoo','Miniver','Miss Fajardose','Modak','Modern Antiqua','Molengo','Monda','Monofett','Monoton','Monsieur La Doulaise','Montaga','Montez','Montserrat','Montserrat Alternates','Montserrat Subrayada','Moul','Moulpali','Mountains of Christmas','Mouse Memoirs','Mr Bedfort','Mr Dafoe','Mr De Haviland','Mrs Saint Delafield','Mrs Sheppards','Muli','Mystery Quest','NTR','Neucha','Neuton','New Rocker','News Cycle','Niconne','Nixie One','Nobile','Nokora','Norican','Nosifer','Nothing You Could Do','Noticia Text','Noto Sans','Noto Serif','Nova Cut','Nova Flat','Nova Mono','Nova Oval','Nova Round','Nova Script','Nova Slim','Nova Square','Numans','Nunito','Odor Mean Chey','Offside','Old Standard TT','Oldenburg','Oleo Script','Oleo Script Swash Caps','Open Sans','Oranienbaum','Orbitron','Oregano','Orienta','Original Surfer','Oswald','Over the Rainbow','Overlock','Overlock SC','Ovo','Oxygen','Oxygen Mono','PT Mono','PT Sans','PT Sans Caption','PT Sans Narrow','PT Serif','PT Serif Caption','Pacifico','Palanquin','Palanquin Dark','Paprika','Parisienne','Passero One','Passion One','Pathway Gothic One','Patrick Hand','Patrick Hand SC','Patua One','Paytone One','Peddana','Peralta','Permanent Marker','Petit Formal Script','Petrona','Philosopher','Piedra','Pinyon Script','Pirata One','Plaster','Play','Playball','Playfair Display','Playfair Display SC','Podkova','Poiret One','Poller One','Poly','Pompiere','Pontano Sans','Port Lligat Sans','Port Lligat Slab','Pragati Narrow','Prata','Preahvihear','Press Start 2P','Princess Sofia','Prociono','Prosto One','Puritan','Purple Purse','Quando','Quantico','Quattrocento','Quattrocento Sans','Questrial','Quicksand','Quintessential','Qwigley','Racing Sans One','Radley','Rajdhani','Raleway','Raleway Dots','Ramabhadra','Ramaraja','Rambla','Rammetto One','Ranchers','Rancho','Ranga','Rationale','Ravi Prakash','Redressed','Reenie Beanie','Revalia','Ribeye','Ribeye Marrow','Righteous','Risque','Roboto','Roboto Condensed','Roboto Slab','Rochester','Rock Salt','Rokkitt','Romanesco','Ropa Sans','Rosario','Rosarivo','Rouge Script','Rozha One','Rubik Mono One','Rubik One','Ruda','Rufina','Ruge Boogie','Ruluko','Rum Raisin','Ruslan Display','Russo One','Ruthie','Rye','Sacramento','Sail','Salsa','Sanchez','Sancreek','Sansita One','Sarina','Sarpanch','Satisfy','Scada','Scheherazade','Schoolbell','Seaweed Script','Sevillana','Seymour One','Shadows Into Light','Shadows Into Light Two','Shanti','Share','Share Tech','Share Tech Mono','Shojumaru','Short Stack','Siemreap','Sigmar One','Signika','Signika Negative','Simonetta','Sintony','Sirin Stencil','Six Caps','Skranji','Slabo 13px','Slabo 27px','Slackey','Smokum','Smythe','Sniglet','Snippet','Snowburst One','Sofadi One','Sofia','Sonsie One','Sorts Mill Goudy','Source Code Pro','Source Sans Pro','Source Serif Pro','Special Elite','Spicy Rice','Spinnaker','Spirax','Squada One','Sree Krushnadevaraya','Stalemate','Stalinist One','Stardos Stencil','Stint Ultra Condensed','Stint Ultra Expanded','Stoke','Strait','Sue Ellen Francisco','Sumana','Sunshiney','Supermercado One','Suranna','Suravaram','Suwannaphum','Swanky and Moo Moo','Syncopate','Tangerine','Taprom','Tauri','Teko','Telex','Tenali Ramakrishna','Tenor Sans','Text Me One','The Girl Next Door','Tienne','Timmana','Tinos','Titan One','Titillium Web','Trade Winds','Trocchi','Trochut','Trykker','Tulpen One','Ubuntu','Ubuntu Condensed','Ubuntu Mono','Ultra','Uncial Antiqua','Underdog','Unica One','UnifrakturMaguntia','Unkempt','Unlock','Unna','VT323','Vampiro One','Varela','Varela Round','Vast Shadow','Vesper Libre','Vibur','Vidaloka','Viga','Voces','Volkhov','Vollkorn','Voltaire','Waiting for the Sunrise','Wallpoet','Walter Turncoat','Warnes','Wellfleet','Wendy One','Wire One','Yanone Kaffeesatz','Yellowtail','Yeseva One','Yesteryear','Zeyada');
		}
	}

	public function getDatabaseParams( $source ) {

		$model = $this->getModel('databases');
		$dbNames = array();
		$dbTables = array();
		$dbFields = array();
		$error = false;

		$dbName = UtilsPyt::getArrayValue($source, 'db_name');
		$dbTable = UtilsPyt::getArrayValue($source, 'tbl_name');
		$databases = $model->getDBNames();

		if(isset($databases) && is_array($databases)) {
			$dbNames = $databases;

			$dbNames[] = $this->dbExternalValue;
			if (empty($dbName) || !in_array($dbName, $dbNames)) {
				$dbName = $dbNames[0];
				$source['db_name'] = $dbName;
			}
			$connected = $model->connectToDatabase($source);
			if ($connected === true) {
				$dbTables = $model->getDBTables();
				array_unshift($dbTables, $this->dbSQLValue);
				if(!isset($dbTable) || !in_array($dbTable, $dbTables)) {
					$dbTable = $dbTables[0];
				}
				if($dbTable != $this->dbSQLValue) {
					$dbFields = $model->getTableFields($dbTable);
				}
			}
		}

		return array('databases' => $dbNames, 'tables' => $dbTables, 'fields' => $dbFields, 'dbName' => $dbName, 'dbTable' => $dbTable);
	}

	public function getCellsData($data, $params) {
		$tableId = ReqPyt::getVar('tableId');
		$tableType = ReqPyt::getVar('tableType');

		$request = ReqPyt::get('post');
		$source = UtilsPyt::jsonDecode(stripslashes(UtilsPyt::getArrayValue($request, 'source')));
		$source = UtilsPyt::getArrayValue($source, 'source', array(), 2);

		if (!empty($source)) {
			switch ($tableType) {
				case 1:
					$data = FramePyt::_()->getModule('tables')->getModel('cells')->getCellsData($tableId, $params);
					break;
				case 3:
					$data = $this->getModel('databases')->getCellsData($tableId, $source, $params);
					break;
				default:
					break;
			}
		}

		return $data;
	}

	public function getRangeData($tableId, $range, $withTitles = true) {

		$table = FramePyt::_()->getModule('tables')->getModel()->getTableData($tableId);
		if ($table) {
			$tableType = $table['type'];

			switch ($tableType) {
				case 0:
					return $this->getModel('cellspro')->getRangeData($table, $range, $withTitles);
					break;
				case 1:
					$tableId = FramePyt::_()->getModule('import')->importGoogleSpreadsheet($tableId, UtilsPyt::getArrayValue($table, 'source', array(), 2));
					if ($tableId) {
						return $this->getModel('cellspro')->getRangeData($table, $range, $withTitles);
					}
					break;
				case 3:
					return $this->getModel('databases')->getRangeData($table, $range, $withTitles);
					break;
				default:
					break;
			}
		}
		return false;
	}

	public function saveNewTable( $id ) {
		return FramePyt::_()->getModule('import')->importTableData($id, ReqPyt::get('files'), ReqPyt::get('post'));
	}

	public function getFrontRows($data, $table, $params) {
		$data = $this->getModel('cellspro')->getFrontRowsPro($table, $params);
		return $data;
	}
	public function addShortcodeAttributes( $params ) {
		$vars = array();
		foreach ($params as $key => $value) {
			if (stripos($key, 'sql') === 0) {
				$vars[$key] = $value;
				unset($params[$key]);
			}
		}
		$params['scAttributes'] = $vars;
		return $params;
	}
}
