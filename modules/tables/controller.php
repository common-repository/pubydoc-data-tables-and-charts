<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TablesControllerPyt extends ControllerPyt {

	protected $_code = 'tables';

	public function getNoncedMethods() {
		return array('saveNewTable', 'saveTableTitle', 'saveTableData', 'saveTableOptions', 'removeTable', 'saveTableAddCss', 'saveTableAddJs');
	}

	protected function _prepareModelBeforeListSelect( $model ) {
		return $model->setSelectFields('id, title, type');
	}

	public function _prepareListForTbl($data) {
		return $this->getModel()->prepareTablesList($data);	
	}
	
	public function saveNewTable() {
		if (empty(ReqPyt::getVar('creation'))) {
			$id = $this->getModel()->saveNew(ReqPyt::get('post'));
		} else {
			$id = DispatcherPyt::applyFilters('saveNewTable', 0);
		}

		$res = new ResponsePyt();
		if ( false != $id ) {
			$res->addMessage(esc_html__('Done', 'publish-your-table'));
			$res->addData('edit_link', FramePyt::_()->getModule('adminmenu')->getEditLink($id, $this->_code));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function saveTableTitle() {
		$res = new ResponsePyt();
		if ($this->getModel()->saveTitle(ReqPyt::getVar('id'), ReqPyt::getVar('title'))) {
			$res->addMessage(esc_html__('Title saved successfully', 'publish-your-table'));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

	public function getCellsData() {
		$res = new ResponsePyt();
		$params = array(
			'curPage' => ReqPyt::getVar('pq_curpage'),
			'perPage' => ReqPyt::getVar('pq_rpp'),
			'sort' => UtilsPyt::jsonDecode(stripslashes(ReqPyt::getVar('pq_sort'))),
			);

		$tableId = ReqPyt::getVar('tableId');
		$tableType = ReqPyt::getVar('tableType');

		if (empty($tableType)) {
			$result = $this->getModel('cells')->getCellsData($tableId, $params);
		} else {
			$result = DispatcherPyt::applyFilters('getCellsData', array(), $params);
		}

		if ($result) {
			$res->ignoreShellData();
			$res->addData($result);
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

	public function getTablePreview() {
		$res = new ResponsePyt();
		$tableId = ReqPyt::getVar('tableId');  

        $table = $this->getModel('tables')->getTableData($tableId);
        $result = false;
        if ($table) {
        	$options = UtilsPyt::jsonDecode(stripslashes(ReqPyt::getVar('options')));
        	$table['options'] = UtilsPyt::getArrayValue($options, 'options', array(), 2);
        	$table['css'] = ReqPyt::getVar('customCss');
        	$table['view_id'] = 'preview';
        	$view = $this->getView('shortcode');
        	$params = array('isPreview' => true);

	    	$result = $view->renderTableHtml($table, $params);
	    }

		if ($result) {
			$res->html = $result;
			$res->css = $view->addTableStyles($table);
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

	public function getFrontPage() {
		$res = new ResponsePyt();
		$tableId = ReqPyt::getVar('tableId');

		$tables = $this->getModel('tables');
        

        $table = $tables->getTableData($tableId);
        $params = array(
            'isPage' => true,
            'length' => ReqPyt::getVar('length'),
            'start' => ReqPyt::getVar('start'),
            'search' => ReqPyt::getVar('search'),
            'order' => ReqPyt::getVar('order'),
            'columns' => ReqPyt::getVar('columns'),
            'footerIds' => ReqPyt::getVar('footerIds'),
            'colNames' => ReqPyt::getVar('colNames'),
            'scAttributes' => ReqPyt::getVar('scAttributes'),

        );
        
    	$result = false;
    	$table = $this->getModel('tables')->getTableData($tableId);
    	if ($table) {
    		$table['view_id'] = ReqPyt::getVar('viewId');
    		$result = $this->getView('shortcode')->renderTableHtml($table, $params);
    	}	

		if ($result) {
			$res->data = $result['rows'];
			$res->attrs = $result['attrs'];
			$res->recordsTotal = $result['total'];
			$res->recordsFiltered = $result['filtered'];
			$res->css = $result['css'];
			$res->fonts = $result['fonts'];
			$res->draw = ReqPyt::getVar('draw');
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

	public function saveTableData() {
		$res = new ResponsePyt();

		$result = $this->getModel('cells')->saveCellsData(ReqPyt::get('post'));
		if ($result) {
			$res->addMessage(esc_html__('Data saved successfully', 'publish-your-table'));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

	public function saveTableOptions() {
		$res = new ResponsePyt();

		$id = ReqPyt::getVar('tableId');
		$options = UtilsPyt::jsonDecode(stripslashes(ReqPyt::getVar('options')));
		$customCss = ReqPyt::getVar('customCss');
		$data = array(
			'builder' => ReqPyt::getVar('builder'),
			'options' => UtilsPyt::getArrayValue($options, 'options', array(), 2),
			'css' => $customCss
			);

		$result = $this->getModel()->updateSettings($id, $data);
		if ($result) {
			$res->addMessage(esc_html__('Data saved successfully', 'publish-your-table'));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

	public function saveTableAddCss() {
		$res = new ResponsePyt();

		$id = ReqPyt::getVar('tableId');
		$request = ReqPyt::get('post');
		$data = array(
			'add_css' => UtilsPyt::getArrayValue($request, 'add_css')
			);

		$result = $this->getModel()->updateSettings($id, $data);
		if ($result) {
			$res->addMessage(esc_html__('Data saved successfully', 'publish-your-table'));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

	public function saveTableAddJs() {
		$res = new ResponsePyt();

		$id = ReqPyt::getVar('tableId');
		$request = ReqPyt::get('post');
		$data = array(
			'add_js' => UtilsPyt::getArrayValue($request, 'add_js')
			);

		$result = $this->getModel()->updateSettings($id, $data);
		if ($result) {
			$res->addMessage(esc_html__('Data saved successfully', 'publish-your-table'));
		} else {
			$res->pushError(FramePyt::_()->getErrors());
		}
		return $res->ajaxExec();
	}

	public function removeTable() {
    	$res = new ResponsePyt();
        $id = ReqPyt::getVar('tableId');
        
        if (is_numeric($id)) {
        	$result = $this->getModel()->delete($id);
        	if ($result) {
				$res->addMessage(esc_html__('Table deleted', 'publish-your-table'));
				$res->addData('link', FramePyt::_()->getModule('adminmenu')->getTabUrl());
			} else {
				$res->pushError(FramePyt::_()->getErrors());
			}
        } else {
        	$res->pushError(esc_html__('Table Id error detected', 'publish-your-table'));
        }
        return $res->ajaxExec();
    }
    public function clearData() {
    	$res = new ResponsePyt();
        $id = ReqPyt::getVar('tableId');
        
        if (is_numeric($id)) {
        	$result = $this->getModel('cells')->clearCellsData($id);
        	if ($result) {
				$res->addMessage(esc_html__('Table cleared', 'publish-your-table'));
			} else {
				$res->pushError(FramePyt::_()->getErrors());
			}
        } else {
        	$res->pushError(esc_html__('Table Id error detected', 'publish-your-table'));
        }
        return $res->ajaxExec();
    }
    public function cloneTable() {
    	$res = new ResponsePyt();
        $id = ReqPyt::getVar('tableId');
        
        if (is_numeric($id)) {
        	$newId = $this->getModel()->cloneTable($id);
        	if ($newId) {
				$res->addMessage(esc_html__('Table cloned', 'publish-your-table'));
				$res->addData('link', FramePyt::_()->getModule('adminmenu')->getEditLink($newId, $this->_code));
			} else {
				$res->pushError(FramePyt::_()->getErrors());
			}
        } else {
        	$res->pushError(esc_html__('Table Id error detected', 'publish-your-table'));
        }
        return $res->ajaxExec();
    }

}
