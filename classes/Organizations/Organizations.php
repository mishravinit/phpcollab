<?php


namespace phpCollab\Organizations;

use phpCollab\Database;
use Exception;

/**
 * Class Organizations
 * @package phpCollab\Organizations
 */
class Organizations
{
    protected $organizations_gateway;
    protected $db;

    /**
     * Organizations constructor.
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->organizations_gateway = new OrganizationsGateway($this->db);
    }

    /**
     * @param $clientName
     * @return mixed
     */
    public function checkIfClientExistsByName($clientName)
    {
        $clientName = filter_var($clientName, FILTER_SANITIZE_STRING);

        $data = $this->organizations_gateway->getClientByName($clientName);

        return $data;
    }

    /**
     * @param $clientId
     * @return mixed
     */
    public function checkIfClientExistsById($clientId)
    {
        $clientId = filter_var($clientId, FILTER_VALIDATE_INT);

        $data = $this->organizations_gateway->getClientById($clientId);

        return $data;
    }

    /**
     * @param null $sorting
     * @return mixed
     */
    public function getListOfOrganizations($sorting = null)
    {
        $data = $this->organizations_gateway->getAllOrganizations($sorting);

        return $data;
    }

    /**
     * @param $orgId
     * @return mixed
     */
    public function getOrganizationsOrderedByName($orgId)
    {
        $data = $this->organizations_gateway->getOrganizationsOrderedByName($orgId);

        return $data;
    }

    /**
     * @param $orgId
     * @return mixed
     */
    public function getOrganizationById($orgId)
    {
        $orgId = filter_var($orgId, FILTER_VALIDATE_INT);

        $data = $this->organizations_gateway->getClientById($orgId);

        return $data;

    }

    /**
     * @param $orgId
     * @param $ownerId
     * @return mixed
     */
    public function getOrganizationByIdAndOwner($orgId, $ownerId) {
        $orgId = filter_var($orgId, FILTER_VALIDATE_INT);
        $ownerId = filter_var($ownerId, FILTER_VALIDATE_INT);

        $data = $this->organizations_gateway->getOrgByIdAndOwner($orgId, $ownerId);

        return $data;
    }

    /**
     * @param $organizationInfo
     * @return mixed
     */
    public function updateOrganizationInformation($organizationInfo)
    {
        return $this->organizations_gateway->updateOrganizationInformation($organizationInfo);
    }

    /**
     * @param $orgId
     * @param $logoExtension
     * @return mixed
     */
    public function setLogoExtensionByOrgId($orgId, $logoExtension)
    {
        return $this->organizations_gateway->setLogoExtensionByOrgId($orgId, $logoExtension);
    }

    /**
     * @param $clientId
     * @return mixed
     * @throws Exception
     */
    public function deleteClient($clientId)
    {
        if (empty($clientId)) {
            throw new Exception('Invalid client id');
        }

        return $this->organizations_gateway->deleteClient($clientId);
    }
}
