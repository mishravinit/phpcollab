<?php


namespace phpCollab\Notifications;

use phpCollab\Database;

/**
 * Class NotificationsGateway
 * @package phpCollab\Notifications
 */
class NotificationsGateway
{
    protected $db;
    protected $initrequest;
    protected $tableCollab;

    /**
     * NotificationsGateway constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->initrequest = $GLOBALS['initrequest'];
        $this->tableCollab = $GLOBALS['tableCollab'];
    }

    /**
     * @param $memberId
     * @return mixed
     */
    public function getNotificationsWhereMemeberIn($memberId)
    {
        $memberId = explode(',', $memberId);
        $placeholders = str_repeat ('?, ', count($memberId)-1) . '?';
        $whereStatement = " WHERE noti.member IN($placeholders)";
        $this->db->query($this->initrequest["notifications"] . $whereStatement);
        $this->db->execute($memberId);
        return $this->db->fetchAll();

    }

    /**
     * @param $memberId
     * @return mixed
     */
    public function deleteNotificationsByMemberIdIn($memberId)
    {
        // Generate placeholders
        $memberId = explode(',', $memberId);
        $placeholders = str_repeat('?, ', count($memberId) - 1) . '?';
        $sql = "DELETE FROM {$this->tableCollab['notifications']} WHERE member IN ($placeholders)";
        $this->db->query($sql);
        return $this->db->execute($memberId);

    }
}
