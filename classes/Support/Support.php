<?php


namespace phpCollab\Support;

use phpCollab\Database;

/**
 * Class Support
 * @package phpCollab\Support
 */
class Support
{
    protected $support_gateway;
    protected $db;

    /**
     * Support constructor.
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->support_gateway = new SupportGateway($this->db);
    }

    /**
     * @param $supportRequestIds
     * @return mixed
     */
    public function deleteSupportRequests($supportRequestIds)
    {
        return $this->support_gateway->deleteSupportRequests($supportRequestIds);
    }

    /**
     * @param $projectIds
     * @return mixed
     */
    public function deleteSupportRequestsByProjectId($projectIds)
    {
        return $this->support_gateway->deleteSupportRequestsByProjectId($projectIds);
    }

    /**
     * @param $requestIds
     * @return mixed
     */
    public function deleteSupportPostsByRequestId($requestIds)
    {
        return $this->support_gateway->deleteSupportPostsByRequestId($requestIds);
    }

    /**
     * @param $supportPostIds
     * @return mixed
     */
    public function deleteSupportPostsById($supportPostIds)
    {
        return $this->support_gateway->deleteSupportPostsById($supportPostIds);
    }

    /**
     * @param $projectIds
     * @return mixed
     */
    public function deleteSupportPostsByProjectId($projectIds)
    {
        return $this->support_gateway->deleteSupportPostsByProjectId($projectIds);
    }

}