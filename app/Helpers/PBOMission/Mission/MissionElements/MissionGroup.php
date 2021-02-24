<?php
  namespace App\Helpers\PBOMission\Mission\MissionElements;

  use App\Helpers\PBOMission\Mission\MissionElements\MissionUnit;
  use App\Helpers\PBOMission\Mission\SQMClass;

  define('MISSION_GROUP_EXPORT_KEYS', array('id','name','side', 'orbatParent'));

  class MissionGroup {
    // All groups
    public $id;
    public $unitsCount = 0;
    public $waypointsCount = 0;
    public $playable = false;
    // Filled only if playable units present
    public $side;
    public $name;
    public $playableUnits = array();
    public $crewLinks = array();
    public $orbatParent;

    function __construct(SQMClass $group) {
      $this->id = $group->attribute('id');
      if (!$entities = $group->class('Entities')) return;

      foreach ($entities->classes as $entitie) {
        $dataType = $entitie->attribute('dataType');

        if ($dataType == 'Object') {
          $unit = new MissionUnit($entitie);
          if ($unit->playable) $this->playableUnits[] = $unit;
          $this->unitsCount++;
          continue;
        }

        if ($dataType == 'Waypoint') {
          $this->waypointsCount++;
          continue;
        }
      }

      // Is group playable
      if (!isset($this->playableUnits) || !$this->playableUnits) return;
      $this->playable = true;

      // Get lowercased side name
      $this->side = $group->attribute('side');
      if ($this->side) $this->side = mb_strtolower($this->side);

      // Get group custom name and orbatParent
      if ($customAttributes = $group->class('CustomAttributes')) 
      {
        foreach ($customAttributes->classes as $customAttribute) 
        {
          if ($customAttribute->attribute('property') == 'groupID') 
          {
            if (!$customAttribute->hasClassPath('Value','data')) break;
            $this->name = $customAttribute->class('Value')->class('data')->attribute('value');
          } 
          elseif ($customAttribute->attribute('property') == 'TMF_OrbatParent') 
          {
            if (!$customAttribute->hasClassPath('Value','data')) break;
            $this->orbatParent = intval($customAttribute->class('Value')->class('data')->attribute('value'));
          }
        }
      }

      // Process crew links
      if (!$group->hasClassPath('CrewLinks', 'Links')) return;
      foreach ($group->class('CrewLinks')->class('Links')->classes as $link) {
        $linkUnit = $link->attribute('item0');
        $linkVehicle = $link->attribute('item1');
        if (!isset($linkUnit, $linkVehicle)) continue;
        $this->crewLinks[$linkUnit] = $linkVehicle;
      }
    }

    public function export(): array {
      $data = array();

      foreach (MISSION_GROUP_EXPORT_KEYS as $key)
        if (isset($this->{$key})) $data[$key] = $this->{$key};

      $data['units'] = array();
      foreach ($this->playableUnits as $unit)
        $data['units'][] = $unit->export();

      return $data;
    }
  }
?>
