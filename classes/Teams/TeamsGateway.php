<?php


namespace phpCollab\Teams;

use phpCollab\Database;

/**
 * Class TeamsGateway
 * @package phpCollab\Teams
 */
class TeamsGateway
{
    protected $db;
    protected $initrequest;
    protected $tableCollab;

    /**
     * TeamsGateway constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->initrequest = $GLOBALS['initrequest'];
        $this->tableCollab = $GLOBALS['tableCollab'];
    }

    /**
     * @param $projectId
     * @param $memberId
     * @return mixed
     */
    public function getTeamByProjectIdAndTeamMember($projectId, $memberId)
    {
        $whereStatement = " WHERE tea.project = :project_id AND tea.member = :member_id";
        $this->db->query($this->initrequest["teams"] . $whereStatement);
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':member_id', $memberId);
        $results = $this->db->resultset();
        return $results;
    }

    /**
     * @param $projectId
     * @param $memberId
     * @return mixed
     */
    public function getTeamByProjectIdAndTeamMemberAndStatusIsNotCompletedOrSuspendedAndIsNotPublished($projectId, $memberId)
    {
        $whereStatement = " WHERE tea.member = :member_id AND pro.id = :project_id AND pro.status IN(0,2,3) AND pro.published = '0'";
        $this->db->query($this->initrequest["teams"] . $whereStatement);
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':member_id', $memberId);
        return $this->db->resultset();
    }

    /**
     * @param $memberId
     * @return mixed
     */
    public function getTeamByMemberIdAndStatusIsNotCompletedAndIsNotPublished($memberId)
    {
        $whereStatement = " WHERE tea.member = :member_id AND pro.status IN(0,2,3) AND pro.published = 0 ORDER BY pro.name";
        $this->db->query($this->initrequest["teams"] . $whereStatement);
        $this->db->bind(':member_id', $memberId);
        return $this->db->resultset();
    }

    /**
     * @param $memberId
     * @return mixed
     */
    public function getTeamByMemberId($memberId)
    {
        $whereStatement = " WHERE tea.member = :member_id ORDER BY pro.name";
        $this->db->query($this->initrequest["teams"] . $whereStatement);
        $this->db->bind(':member_id', $memberId);
        $results = $this->db->resultset();
        return $results;
    }

    /**
     * @param $memberId
     * @param $orgId
     * @return mixed
     */
    public function getTeamByTeamMemberAndOrgId($memberId, $orgId)
    {
        $whereStatement = "WHERE tea.member = :member_id AND org2.id = :org_id";
        $this->db->query($this->initrequest["teams"] . $whereStatement);
        $this->db->bind(':org_id', $orgId);
        $this->db->bind(':member_id', $memberId);
        $results = $this->db->resultset();
        return $results;
    }

    /**
     * @param $projectId
     * @param null $sorting
     * @return mixed
     * @internal param $orderBy
     */
    public function getTeamByProjectIdAndOrderBy($projectId, $sorting = null)
    {
        $whereStatement = " WHERE tea.project = :project_id";

        $sql = $this->initrequest["teams"] . $whereStatement . $this->orderBy($sorting);
        $this->db->query($sql);
        $this->db->bind(':project_id', $projectId);
        $results = $this->db->resultset();

        return $results;
    }

    /**
     * @param $projectId
     * @param null $sorting
     * @return mixed
     */
    public function getClientTeamMembersByProject($projectId, $sorting = null)
    {
        $whereStatement = " WHERE tea.project = :project_id  AND mem.profil = 3";
        $this->db->query($this->initrequest["teams"] . $whereStatement . $this->orderBy($sorting));
        $this->db->bind(':project_id', $projectId);
        $results = $this->db->resultset();

        return $results;
    }

    /**
     * @param $projectId
     * @param $memberId
     * @return mixed
     */
    public function isTeamMember($projectId, $memberId)
    {
        $whereStatement = " WHERE tea.project = :project_id AND tea.member = :member_id";

        $sql = $this->initrequest["teams"] . $whereStatement;

        $this->db->query($sql);
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':member_id', $memberId);
        return $this->db->resultset();
    }

    /**
     * @param $memberId
     * @return mixed
     */
    public function getTeamsImAMemberOf($memberId)
    {
        $sql = "SELECT tea.id FROM {$this->tableCollab["teams"]} tea LEFT OUTER JOIN {$this->tableCollab["projects"]} pro ON pro.id = tea.project WHERE tea.member = :member_id AND pro.status IN(0,2,3)";
        $this->db->query($sql);
        $this->db->bind(':member_id', $memberId);
        return $this->db->resultset();
    }

    /**
     * @param $projectId
     * @param $memberIds
     * @return mixed
     */
    public function publishTeams($projectId, $memberIds)
    {
        if ( strpos($memberIds, ',') ) {
            $memberIds = explode(',', $memberIds);
            $placeholders = str_repeat ('?, ', count($memberIds)-1) . '?';
            $sql = "UPDATE {$this->tableCollab["teams"]} SET published = 0 WHERE member IN($placeholders) AND project = ?";

            array_push($memberIds, $projectId);
            $this->db->query($sql);
            return $this->db->execute($memberIds);
        } else {
            $sql = "UPDATE {$this->tableCollab["teams"]} SET published = 0 WHERE member = :member_id AND project = :project_id";
            $this->db->query($sql);
            $this->db->bind(':member_id', $memberIds);
            $this->db->bind(':project_id', $projectId);
            return $this->db->execute();
        }
    }

    /**
     * @param $projectId
     * @param $memberIds
     * @return mixed
     */
    public function unPublishTeams($projectId, $memberIds)
    {
        if ( strpos($memberIds, ',') ) {
            $memberIds = explode(',', $memberIds);
            $placeholders = str_repeat ('?, ', count($memberIds)-1) . '?';
            $sql = "UPDATE {$this->tableCollab["teams"]} SET published = 1 WHERE member IN($placeholders) AND project = ?";

            array_push($memberIds, $projectId);
            $this->db->query($sql);
            return $this->db->execute($memberIds);
        } else {
            $sql = "UPDATE {$this->tableCollab["teams"]} SET published = 1 WHERE member = :member_id AND project = :project_id";
            $this->db->query($sql);
            $this->db->bind(':member_id', $memberIds);
            $this->db->bind(':project_id', $projectId);
            return $this->db->execute();
        }

    }

    /**
     * @param $projectId
     * @param $memberId
     * @return mixed
     */
    public function deleteFromTeamsWhereProjectIdEqualsAndMemberIdIn($projectId, $memberId)
    {
        // Generate placeholders
        $placeholders = str_repeat ('?, ', count($memberId)-1) . '?';

        $sql = "DELETE FROM {$this->tableCollab["teams"]} WHERE project = ? AND member IN($placeholders)";

        // Prepend the project id value
        if (is_array($placeholders)) {
            array_unshift($placeholders,$projectId);
        }
        $this->db->query($sql);
        $this->db->execute($placeholders);
        return $this->db->fetchAll();


    }

    /**
     * @param $projectId
     * @return mixed
     */
    public function deleteFromTeamsWhereProjectIdIn($projectId)
    {
        // Generate placeholders
        $projectId = explode(',', $projectId);
        $placeholders = str_repeat('?, ', count($projectId) - 1) . '?';
        $sql = "DELETE FROM {$this->tableCollab['teams']} WHERE project IN ($placeholders)";
        $this->db->query($sql);
        return $this->db->execute($projectId);
    }

    /**
     * @param $memberId
     * @return mixed
     */
    public function deleteFromTeamsWhereMemberIdIn($memberId)
    {
        // Generate placeholders
        $memberId = explode(',', $memberId);
        $placeholders = str_repeat('?, ', count($memberId) - 1) . '?';
        $sql = "DELETE FROM {$this->tableCollab['teams']} WHERE member IN ($placeholders)";
        $this->db->query($sql);
        return $this->db->execute($memberId);
    }

    /**
     * @param $projectId
     * @param $memberId
     * @param $published
     * @param $authorized
     * @return mixed
     */
    public function addTeam($projectId, $memberId, $published, $authorized)
    {
        $sql = "INSERT INTO {$this->tableCollab["teams"]} (project,member,published,authorized) VALUES(:project,:member,:published,:authorized)";
        $this->db->query($sql);
        $this->db->bind(':project', $projectId);
        $this->db->bind(':member', $memberId);
        $this->db->bind(':published', $published);
        $this->db->bind(':authorized', $authorized);
        return $this->db->execute();
    }

    /**
     * @param $sorting
     * @return string
     */
    private function orderBy($sorting)
    {
        if (!is_null($sorting)) {
            $allowedOrderedBy = [];
            $pieces = explode(' ', $sorting);

            if ($pieces) {
                $key = array_search($pieces[0], $allowedOrderedBy);

                if ($key !== false) {
                    $order = $allowedOrderedBy[$key];
                    return " ORDER BY $order $pieces[1]";
                }
            }
        }

        return '';
    }
}
