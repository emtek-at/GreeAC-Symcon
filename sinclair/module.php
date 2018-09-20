<?
// Klassendefinition
class sinclair extends IPSModule {

    // Der Konstruktor des Moduls
    // Überschreibt den Standard Kontruktor von IPS
    public function __construct($InstanceID) {
        // Diese Zeile nicht löschen
        parent::__construct($InstanceID);

        // Selbsterstellter Code
        //UDP Socket = {82347F20-F541-41E1-AC5B-A636FD3AE2D8}
        //$this->ConnectParent("{82347F20-F541-41E1-AC5B-A636FD3AE2D8}");
    }

    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();

        $this->RegisterPropertyString("host", "");
        $this->RegisterPropertyInteger("statusTimer", 60*2);

        $this->RegisterVariableBoolean("power", $this->Translate("varPower"));
        $this->RegisterVariableString("lastUpdate", $this->Translate("varLastUpdate"));
        $this->RegisterVariableString("macAddress", $this->Translate("varMacAddress"));
        $this->RegisterVariableString("name", $this->Translate("varName"));

        $this->RegisterTimer("status_UpdateTimer", 0, 'SAW_getStatus($_IPS[\'TARGET\']);');

        $this->RequireParent("{82347F20-F541-41E1-AC5B-A636FD3AE2D8}");
    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        $host = $this->ReadPropertyString("host");
        if (strlen($host) > 0)
        {
            //Instanz ist aktiv
            //$this->SetStatus(101);

            $this->SendDebug('host', $host, 0);

            $statusInterval = $this->ReadPropertyInteger("statusTimer");
            $this->SendDebug('Update Status Interval', $statusInterval.' sec', 0);

            //$this->SetTimerInterval('status_UpdateTimer', $statusInterval*1000);

            $this->SetStatus(102);
        }
        else
        {
            //Instanz ist aktiv
            $this->SetStatus(201);
        }
    }

    public function GetConfigurationForParent(){
        $JsonArray = array( "Host" => $this->ReadPropertyString('host'), "Port" => 7000, "Open" => true);
        $Json = json_encode($JsonArray);
        return $Json;
    }


    public function ReceiveData($JSONString){
        $this->SendDebug('ReceiveData', $JSONString, 0);
        $rec = json_decode($JSONString);
        $this->SendDebug('DataId', $rec->DataId, 0);
        if($rec->DataId == '018EF6B5-AB94-40C6-AA53-46943E824ACF') {
            $data = json_decode($rec->Buffer);
            $this->SendDebug('AC MAC', $data->mac, 0);
            $this->SendDebug('AC Name', $data->name, 0);
        }
    }


    public function test() {
        // Selbsterstellter Code
        $arr = array('t' => 'scan');
        //$this->SendDataToParent(json_encode($arr));
        $r = $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => "{\"t\":\"scan\"}")));
        $this->SendDebug('sdp return', $r, 0);
    }


    public function getStatus(){
        $this->SendDebug('getStatus', '0', 0);

        SetValueString($this->GetIDForIdent('lastUpdate'), date("Y-m-d H:i:s"));
    }
}
?>