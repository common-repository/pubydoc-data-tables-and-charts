<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DiagramsControllerPyt extends ControllerPyt {
	public function getNoncedMethods() {
		return array('saveDiagram', 'cloneDiagram');
	}
	public function _prepareListForTbl( $data ) {
		return $this->getModel()->prepareTablesList($data);	
	}

	public function getListForTbl() {
		$res = new ResponsePyt();
		$res->ignoreShellData();
		$model = $this->getModel();

		$params = ReqPyt::get('post');

		$length = UtilsPyt::getArrayValue($params, 'length', 10, 1);
		$start = UtilsPyt::getArrayValue($params, 'start', 0, 1);
		$search = UtilsPyt::getArrayValue(UtilsPyt::getArrayValue($params, 'search', array(), 2), 'value');

		if (!empty($search)) {
			$model->addWhere(array('additionalCondition' => "title like '%" . $search . "%'"));
		}
		$order = UtilsPyt::getArrayValue($params, 'order', array(), 2);
		$orderBy = 'id';
		$sortOrder = 'DESC';
		if (isset($order[0])) {
			$orderBy = UtilsPyt::getArrayValue($order[0], 'column', $orderBy, 1);
			$sortOrder = UtilsPyt::getArrayValue($order[0], 'dir', $sortOrder);
		}

		// Get total pages count for current request
		$totalCount = $model->getCount(array('clear' => array('selectFields')));
		if ($length > 0) {
			if ($start >= $totalCount) {
				$start = 0;
			}
			$model->setLimit($start . ', ' . $length);
		}

		$model->setOrderBy($orderBy)->setSortOrder($sortOrder);
		$data = $this->_prepareModelBeforeListSelect($model)->getFromTbl();
		
		$data = empty($data) ? array() : $this->_prepareListForTbl($data);
		$res->data = $data;

		$res->recordsFiltered = $totalCount;
		$res->recordsTotal = $totalCount;
		$res->draw = UtilsPyt::getArrayValue($params, 'draw', 0, 1);

		$res = DispatcherPyt::applyFilters($this->getCode() . '_getListForTblResults', $res);
		$res->ajaxExec();

	}
	public function saveDiagram() {
		$res = new ResponsePyt();

		$id = (int) ReqPyt::getVar('diagramId');
		$settings = UtilsPyt::jsonDecode(stripslashes(ReqPyt::getVar('settings')));
		$config = UtilsPyt::jsonDecode(stripslashes(ReqPyt::getVar('config')));
		$status = ReqPyt::getVar('status');
		
		if (!empty($files['imgFile'])) {
			$imgFile = $files['imgFile']; 
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once(ABSPATH . 'wp-admin/includes/file.php');
			}
			add_filter('upload_dir', array($this, 'getDiagramPreviewPath'));

			$upload = wp_handle_upload($imgFile, array('test_form' => false));

			remove_filter('upload_dir', array($this, 'getDiagramPreviewPath'));
		}

		$result = $this->getModel()->saveDiagram($id, $status, $settings, $config);
		if ($result) {
			$files = ReqPyt::get('files');
			if (!empty($files['imgFile'])) {
				$imgFile = $files['imgFile'];
				$imgFile['name'] = $result . '.png';
				if ( ! function_exists( 'wp_handle_upload' ) ) {
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				}
				add_filter('upload_dir', array($this, 'getDiagramPreviewPath'));

				$upload = wp_handle_upload($imgFile, array('test_form' => false, 'unique_filename_callback' => array($this, 'getDiagramFilename')));

				remove_filter('upload_dir', array($this, 'getDiagramPreviewPath'));
			}

			$res->addMessage(esc_html__('Data saved successfully', 'publish-your-table'));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function getDiagramPreviewPath( $param ){
		$dir = $this->getModule()->diagramPreviewPath;

		$param['path'] = $param['basedir'] . $dir;
		$param['url'] = $param['baseurl'] . $dir;

		return $param;
	}
	public function getDiagramFilename($dir, $name, $ext) {
    	return $name;
	}
	public function cloneDiagram() {
    	$res = new ResponsePyt();
        $id = ReqPyt::getVar('diagramId');
        
        if (is_numeric($id)) {
        	$newId = $this->getModel()->cloneDiagram($id);
        	if ($newId) {
				$res->addMessage(esc_html__('Diagram cloned', 'publish-your-table'));
				$res->addData('link', FramePyt::_()->getModule('adminmenu')->getMainLink() . '&tab=tables-diagrams&id=' . $newId);
			} else {
				$res->pushError(FramePyt::_()->getErrors());
			}
        } else {
        	$res->pushError(esc_html__('Diagram Id error detected', 'publish-your-table'));
        }
        return $res->ajaxExec();
    }
}
