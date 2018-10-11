<?php

namespace Kanboard\Plugin\Backlog\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Model\ProjectModel;
use Kanboard\Model\SwimlaneModel;
use Kanboard\Model\ColumnModel;
use Kanboard\Model\TaskPositionModel;


/**
 * Backlog Board
 *
 * @package controller
 * @author  creecros
 */
class BacklogBoardController extends BaseController {

    public function set() {
        $projectId = $this->request->getIntegerParam('project_id');

        $this->projectUsesBacklogBoardModel->setBacklogBoard($projectId);
        $this->backlogSwimlane($projectId);
        $this->backlogColumn($projectId);

        $this->flash->success(t('Backlog Board now activated.'));

        $this->response->redirect($this->helper->url->to('BoardViewController', 'show', array('project_id' => $projectId), true));
    }

    public function unset() {
        $projectId = $this->request->getIntegerParam('project_id');

        $this->projectUsesBacklogBoardModel->unsetBacklogBoard($projectId);
        $this->moveTasksOut($projectId);
        $this->removeBacklogSwimlane($projectId);
        $this->removeBacklogColumn($projectId);

        $this->flash->success(t('Backlog Board now deactivated.'));

        $this->response->redirect($this->helper->url->to('BoardViewController', 'show', array('project_id' => $projectId), true));
    }
    
    public function backlogSwimlane($projectId) {
          $this->swimlaneModel->create($projectId, 'Backlog_Swimlane', 'Temporary Swimlane for Backlog Board');  
          $this->swimlaneModel->changePosition($projectId, $this->swimlaneModel->getIdByName($projectId, 'Backlog_Swimlane'), 1);
    }
    
    public function removeBacklogSwimlane($projectId) {
          $this->swimlaneModel->remove($projectId, $this->swimlaneModel->getIdByName($projectId, 'Backlog_Swimlane'));
    }
    
    
    public function backlogColumn($projectId) {
          $this->columnModel->create($projectId, 'Backlog_Board', 0, 'Main Column for Backlog Board', 0);
          $this->columnModel->changePosition($projectId, $this->columnModel->getColumnIdByTitle($projectId, 'Backlog_Board'), 1);
    }
    
    public function removeBacklogColumn($projectId) {
          $this->columnModel->remove($this->columnModel->getColumnIdByTitle($projectId, 'Backlog_Board'));
    }
    
    public function moveTasksOut($projectId) {
          $columnId = $this->columnModel->getColumnIdByTitle($projectId, 'Backlog_Board');
          $allColumns = $this->columnModel->getAll($projectId);
          foreach ($allColumns as $column) { if ($column['position'] === 2) { $column_to = $column['id']; } }
          $allSwimlanes = $this->swimlaneModel->getAll($projectId);
          foreach ($allSwimlanes as $swimlane) { if ($swimlane['position'] === 2) { $swimlane_to = $swimlane['id']; } } 
          $tasksInColumn = $this->projectUsesBacklogBoardModel->getTasksInColumn($projectId, $columnId);
          foreach ($tasksInColumn as $task) { $this->taskPositionModel->movePosition($projectId, $task['id'], $column_to, 1, $swimlane_to, true, false); }
    }

}
