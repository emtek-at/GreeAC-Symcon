<?
abstract class Commands
{
    const none = -1;
    const scan = 0;
    const bind = 1;
    const status = 2;
    const cmd = 3;
}

abstract class DeviceParam
{
    const Power = "Pow";
    const Mode = "Mod";
    const Fanspeed = "WdSpd";
    const Swinger = "SwUpDn";
    const SetTemperature = "SetTem";
    const ActTemperature = "TemSen";
    const OptDry = "Blo";
    const OptHealth = "Health";
    const OptLight = "Lig";
    const OptSleep1 = "SwhSlp";
    const OptSleep2 = "SlpMod";
    const OptEco = "SvSt";
    const OptAir = "Air";
}

// Klassendefinition
class sinclair extends IPSModule {
    const debug = true;
    const defaultCryptKey = 'a3K8Bx%2r8Y7#xDh';


    // Der Konstruktor des Moduls
    // Überschreibt den Standard Kontruktor von IPS
    public function __construct($InstanceID) {
        // Diese Zeile nicht löschen
        parent::__construct($InstanceID);
    }

    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();

        $this->RegisterPropertyString("host", "");
        $this->RegisterPropertyInteger("statusTimer", 60);

        $this->RegisterTimer("status_UpdateTimer", 0, 'Sinclair_getStatus($_IPS[\'TARGET\']);');
        $this->RegisterTimer("resetCmdTimer", 0, 'Sinclair_resetCmd($_IPS[\'TARGET\']);');


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


            if(!IPS_VariableProfileExists('Sinclair.DeviceMode'))
                IPS_CreateVariableProfile('Sinclair.DeviceMode', 1);
            if(!IPS_VariableProfileExists('Sinclair.DeviceFan'))
                IPS_CreateVariableProfile('Sinclair.DeviceFan', 1);
            if(!IPS_VariableProfileExists('Sinclair.DeviceSwinger'))
                IPS_CreateVariableProfile('Sinclair.DeviceSwinger', 1);
            if(!IPS_VariableProfileExists('Sinclair.SetTemp'))
                IPS_CreateVariableProfile('Sinclair.SetTemp', 1);
            if(!IPS_VariableProfileExists('Sinclair.ActTemp'))
                IPS_CreateVariableProfile('Sinclair.ActTemp', 1);

            IPS_SetVariableProfileAssociation('Sinclair.DeviceMode', 0, $this->Translate("paDeviceMode-0"), 'Climate', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceMode', 1, $this->Translate("paDeviceMode-1"), 'Snowflake', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceMode', 2, $this->Translate("paDeviceMode-2"), 'Ventilation', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceMode', 3, $this->Translate("paDeviceMode-3"), 'Drops', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceMode', 4, $this->Translate("paDeviceMode-4"), 'Flame', -1);

            IPS_SetVariableProfileAssociation('Sinclair.DeviceFan', 0, $this->Translate("paDeviceFan-0"), 'Ventilation', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceFan', 1, $this->Translate("paDeviceFan-1"), 'Speedo-0', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceFan', 2, $this->Translate("paDeviceFan-2"), 'Speedo-25', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceFan', 3, $this->Translate("paDeviceFan-3"), 'Speedo-50', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceFan', 4, $this->Translate("paDeviceFan-4"), 'Speedo-75', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceFan', 5, $this->Translate("paDeviceFan-5"), 'Speedo-100', -1);

            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 0, $this->Translate("paDeviceSwinger-0"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 1, $this->Translate("paDeviceSwinger-1"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 2, $this->Translate("paDeviceSwinger-2"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 3, $this->Translate("paDeviceSwinger-3"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 4, $this->Translate("paDeviceSwinger-4"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 5, $this->Translate("paDeviceSwinger-5"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 6, $this->Translate("paDeviceSwinger-6"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 7, $this->Translate("paDeviceSwinger-7"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 8, $this->Translate("paDeviceSwinger-8"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 9, $this->Translate("paDeviceSwinger-9"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 10, $this->Translate("paDeviceSwinger-10"), '', -1);
            IPS_SetVariableProfileAssociation('Sinclair.DeviceSwinger', 11, $this->Translate("paDeviceSwinger-11"), '', -1);

            IPS_SetVariableProfileValues('Sinclair.SetTemp', 17, 27, 1);
            IPS_SetVariableProfileText('Sinclair.SetTemp', '', '°C');

            IPS_SetVariableProfileText('Sinclair.ActTemp', '', '°C');

            $this->RegisterVariableString("name", $this->Translate("varName"), '', 1);
            $this->RegisterVariableBoolean("power", $this->Translate("varPower"), '~Switch', 2);
            $this->RegisterVariableInteger("mode", $this->Translate("varMode"), 'Sinclair.DeviceMode', 3);
            $this->RegisterVariableInteger("setTemp", $this->Translate("varSetTemp"), 'Sinclair.SetTemp', 4);
            $this->RegisterVariableInteger("fan", $this->Translate("varFan"), 'Sinclair.DeviceFan', 5);
            $this->RegisterVariableInteger("swinger", $this->Translate("varSwinger"), 'Sinclair.DeviceSwinger', 6);
            $this->RegisterVariableInteger("actTemp", $this->Translate("varActTemp"), 'Sinclair.ActTemp', 7);
            $this->RegisterVariableBoolean("optDry", $this->Translate("varOptDry"), '~Switch', 8);
            $this->RegisterVariableBoolean("optHealth", $this->Translate("varOptHealth"), '~Switch', 9);
            $this->RegisterVariableBoolean("optLight", $this->Translate("varOptLight"), '~Switch', 10);
            $this->RegisterVariableBoolean("optSleep", $this->Translate("varOptSleep"), '~Switch', 11);
            $this->RegisterVariableBoolean("optEco", $this->Translate("varOptEco"), '~Switch', 12);
            $this->RegisterVariableBoolean("optAir", $this->Translate("varOptAir"), '~Switch', 13);
            $this->RegisterVariableString("lastUpdate", $this->Translate("varLastUpdate"), '', 14);
            $this->RegisterVariableString("macAddress", $this->Translate("varMacAddress"), '', 15);
            $this->RegisterVariableString("deviceKey", $this->Translate("varDeviceKey"), '', 16);
            $this->RegisterVariableInteger("actualCommand", $this->Translate("varActualCommand"), '', 17);

            IPS_SetIcon($this->GetIDForIdent('power'), 'Power');
            IPS_SetIcon($this->GetIDForIdent('swinger'), 'WindSpeed');
            IPS_SetIcon($this->GetIDForIdent('setTemp'), 'Temperature');
            IPS_SetIcon($this->GetIDForIdent('actTemp'), 'Temperature');
            IPS_SetIcon($this->GetIDForIdent('optDry'), 'Drops');
            IPS_SetIcon($this->GetIDForIdent('optHealth'), 'Tree');
            IPS_SetIcon($this->GetIDForIdent('optLight'), 'Light');
            IPS_SetIcon($this->GetIDForIdent('optSleep'), 'Moon');
            IPS_SetIcon($this->GetIDForIdent('optEco'), 'Leaf');
            IPS_SetIcon($this->GetIDForIdent('optAir'), 'WindDirection');
            IPS_SetIcon($this->GetIDForIdent('lastUpdate'), 'Repeat');
            IPS_SetIcon($this->GetIDForIdent('macAddress'), 'Notebook');

            $this->EnableAction("power");
            $this->EnableAction("mode");
            $this->EnableAction("fan");
            $this->EnableAction("swinger");
            $this->EnableAction("setTemp");
            $this->EnableAction("optDry");
            $this->EnableAction("optHealth");
            $this->EnableAction("optLight");
            $this->EnableAction("optSleep");
            $this->EnableAction("optEco");
            $this->EnableAction("optAir");

            IPS_SetHidden($this->GetIDForIdent('deviceKey'), true);
            IPS_SetHidden($this->GetIDForIdent('actualCommand'), true);


            $this->debug('host', $host);

            $statusInterval = $this->ReadPropertyInteger("statusTimer");
            $this->debug('Update Status Interval', $statusInterval.' sec');

            $this->SetTimerInterval('status_UpdateTimer', $statusInterval*1000);
            SetValueInteger($this->GetIDForIdent('actualCommand'), Commands::none);

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

    public function RequestAction($Ident, $Value) {
        $this->debug('RequestAction', $Ident.': '.$Value);
        switch($Ident) {
            case 'power':
                $this->setPower($Value);
                break;
            case 'mode':
                $this->setMode($Value);
                break;
            case 'fan':
                $this->setFan($Value);
                break;
            case 'swinger':
                $this->setSwinger($Value);
                break;
            case 'setTemp':
                $this->setTemp($Value);
                break;
            case 'optDry':
                $this->setOptDry($Value);
                break;
            case 'optHealth':
                $this->setOptHealth($Value);
                break;
            case 'optLight':
                $this->setOptLight($Value);
                break;
            case 'optSleep':
                $this->setOptSleep($Value);
                break;
            case 'optEco':
                $this->setOptEco($Value);
                break;
            case 'optAir':
                $this->setOptAir($Value);
                break;
            default:
                throw new Exception("Invalid ident");
        }

    }

    public function ReceiveData($JSONString){
        $actCmd = GetValueInteger($this->GetIDForIdent('actualCommand'));
        $this->resetCmd();

        $this->debug('ReceiveData', $JSONString);

        $recObj = json_decode($JSONString);
        $bufferObj = json_decode($recObj->Buffer);
        $key = $actCmd < Commands::status ? self::defaultCryptKey : GetValueString($this->GetIDForIdent('deviceKey'));
        $decrypted = $this->decrpyt($bufferObj->pack, $key);
        $decObj = json_decode($decrypted);

        $this->debug('Pack decrypted', $decrypted);

        switch($actCmd){
            case Commands::scan:
                $mac = strtoupper(implode(':', str_split($decObj->mac, 2)));
                SetValueString($this->GetIDForIdent('macAddress'), $mac);
                SetValueString($this->GetIDForIdent('name'), $decObj->name);

                $this->debug('AC MAC', $mac);
                $this->debug('AC Name', $decObj->name);

                $this->deviceBind();
                break;
            case Commands::bind:
                SetValueString($this->GetIDForIdent('deviceKey'), $decObj->key);

                $this->debug('AC DeviceKey', $decObj->key);
                break;
            case Commands::status:
                $this->parseStatus($decObj->cols, $decObj->dat);

                SetValueString($this->GetIDForIdent('lastUpdate'), date("Y-m-d H:i:s"));
                break;
            case Commands::cmd:
                $this->parseStatus($decObj->opt, $decObj->p);
                break;
        }
    }

    private function sendCommand($type, $cmdArr){
 /*       $counter = 0;
        while(GetValueInteger($this->GetIDForIdent('actualCommand')) != Commands::none){
            $counter++;

            if($counter >= 3){
                $this->debug('sendCommand', 'there is another command pending');
                throw new Exception("there is another command pending");
            }

            IPS_Sleep(200);
        }

        $counter = 0;
        while(!$this->HasActiveParent()){
            $counter++;

            if($counter >= 3){
                $this->debug('sendCommand', 'socket is not active');
                throw new Exception("socket is not active");
            }

            IPS_Sleep(200);
        }

        SetValueInteger($this->GetIDForIdent('actualCommand'), $type);
*/
        $this->SetTimerInterval('resetCmdTimer', 500);
        $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => json_encode($cmdArr))));

        return true;
    }


    public function resetCmd(){
        $this->SetTimerInterval('resetCmdTimer', 0);
        SetValueInteger($this->GetIDForIdent('actualCommand'), Commands::none);
    }

    public function initDevice(){
        $arr = array('t' => 'scan');
        $this->sendCommand(Commands::scan, $arr);
    }
    private function deviceBind(){
        $mac = GetValueString($this->GetIDForIdent('macAddress'));
        $mac = strtolower(str_replace(':', '', $mac));
        $pack = array(
            't' => 'bind',
            'uid' => 0,
            'mac' => $mac
        );
        $this->sendCommand(Commands::bind, $this->getRequest($pack, true));
    }
    public function getStatus(){
        //TODO wenn kein device key -> init
        $pack = array(
            't' => 'status',
            'mac' => GetValueString($this->GetIDForIdent('macAddress')),
            'cols' => array(DeviceParam::Power, DeviceParam::Mode, DeviceParam::SetTemperature, DeviceParam::ActTemperature, DeviceParam::Fanspeed, DeviceParam::Swinger, DeviceParam::OptAir, DeviceParam::OptDry, DeviceParam::OptEco, DeviceParam::OptHealth, DeviceParam::OptLight, DeviceParam::OptSleep1)
        );
        $this->sendCommand(Commands::status, $this->getRequest($pack, false));
    }


    public function setPower(bool $newVal){
        $this->debug('setPower', $newVal ? 'on' : 'off');
        $cmd = $this->getCommand(array(DeviceParam::Power, DeviceParam::OptSleep1, DeviceParam::OptSleep2, DeviceParam::OptAir), array($newVal ? 1 : 0, 0, 0, 0));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }
    public function setMode(int $newVal){
        $cmd = $this->getCommand(array(DeviceParam::Mode), array($newVal));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }
    public function setFan(int $newVal){
        $cmd = $this->getCommand(array(DeviceParam::Fanspeed, "Quiet", "Tur"), array($newVal, 0, 0));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }
    public function setSwinger(int $newVal){
        $cmd = $this->getCommand(array(DeviceParam::Swinger), array($newVal));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }
    public function setTemp(int $newVal){
        $cmd = $this->getCommand(array(DeviceParam::SetTemperature), array($newVal));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }
    public function setOptDry(bool $newVal){
        $cmd = $this->getCommand(array(DeviceParam::OptDry), array($newVal ? 1 : 0));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }
    public function setOptHealth(bool $newVal){
        $cmd = $this->getCommand(array(DeviceParam::OptHealth), array($newVal ? 1 : 0));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }
    public function setOptLight(bool $newVal){
        $this->debug('setOptLight', $newVal ? 'on' : 'off');
        $cmd = $this->getCommand(array(DeviceParam::OptLight), array($newVal ? 1 : 0));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }
    public function setOptSleep(bool $newVal){
        $cmd = $this->getCommand(array(DeviceParam::OptSleep1, DeviceParam::OptSleep2), array($newVal ? 1 : 0, $newVal ? 1 : 0));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }
    public function setOptEco(bool $newVal){
        $cmd = $this->getCommand(array(DeviceParam::OptEco), array($newVal ? 1 : 0));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }
    public function setOptAir(bool $newVal){
        $cmd = $this->getCommand(array(DeviceParam::OptAir), array($newVal ? 1 : 0));
        $this->sendCommand(Commands::cmd, $this->getRequest($cmd, false));
    }


    private function parseStatus($cols, $dats){
        for($i=0;$i<count($cols);$i++){
            switch($cols[$i]){
                case DeviceParam::Power:
                    SetValueBoolean($this->GetIDForIdent('power'), $dats[$i]!=0 ? true : false);
                    break;
                case DeviceParam::Mode:
                    SetValueInteger($this->GetIDForIdent('mode'), $dats[$i]);
                    break;
                case DeviceParam::Fanspeed:
                    SetValueInteger($this->GetIDForIdent('fan'), $dats[$i]);
                    break;
                case DeviceParam::Swinger:
                    SetValueInteger($this->GetIDForIdent('swinger'), $dats[$i]);
                    break;
                case DeviceParam::SetTemperature:
                    SetValueInteger($this->GetIDForIdent('setTemp'), $dats[$i]);
                    break;
                case DeviceParam::ActTemperature:
                    SetValueInteger($this->GetIDForIdent('actTemp'), $dats[$i]-40);
                    break;
                case DeviceParam::OptDry:
                    SetValueBoolean($this->GetIDForIdent('optDry'), $dats[$i]!=0 ? true : false);
                    break;
                case DeviceParam::OptHealth:
                    SetValueBoolean($this->GetIDForIdent('optHealth'), $dats[$i]!=0 ? true : false);
                    break;
                case DeviceParam::OptLight:
                    SetValueBoolean($this->GetIDForIdent('optLight'), $dats[$i]!=0 ? true : false);
                    break;
                case DeviceParam::OptSleep1:
                case DeviceParam::OptSleep2:
                    SetValueBoolean($this->GetIDForIdent('optSleep'), $dats[$i]!=0 ? true : false);
                    break;
                case DeviceParam::OptEco:
                    SetValueBoolean($this->GetIDForIdent('optEco'), $dats[$i]!=0 ? true : false);
                    break;
                case DeviceParam::OptAir:
                    SetValueBoolean($this->GetIDForIdent('optAir'), $dats[$i]!=0 ? true : false);
                    break;
            }
        }
    }

    private function getRequest($pack, $bDefKey=true){
        $key = $bDefKey ? self::defaultCryptKey : GetValueString($this->GetIDForIdent('deviceKey'));

        $arr = array(
            'cid' => 'app',
            'i' => $bDefKey ? 1 : 0,
            'pack' => $this->encrypt(json_encode($pack), $key),
            't' => 'pack',
            'tcid' => GetValueString($this->GetIDForIdent('macAddress')),
            'uid' => 22130
        );

        return $arr;
    }
    private function getCommand($opts, $vals){
        $cmd = array(
            't' => 'cmd',
            'opt' => $opts,
            'p' => $vals
        );

        return $cmd;
    }

    private function decrpyt( $message, $key ){
        if($key == '')
            $key = self::defaultCryptKey;

        $decrypt = openssl_decrypt(
            base64_decode( $message ),
            "aes-128-ecb",
            $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
        );

        // remove zero padding
        $decrypt = rtrim( $decrypt, "\x00" );
        // remove PKCS #7 padding
        $decrypt_len = strlen( $decrypt );
        $decrypt_padchar = ord( $decrypt[ $decrypt_len - 1 ] );
        for ( $i = 0; $i < $decrypt_padchar ; $i++ )
        {
            if ( $decrypt_padchar != ord( $decrypt[$decrypt_len - $i - 1] ) )
                break;
        }
        if ( $i != $decrypt_padchar )
            return $decrypt;
        else
            return substr(
                $decrypt,
                0,
                $decrypt_len - $decrypt_padchar
            );
    }
    private function encrypt( $message, $key ){
        if($key == '')
            $key = self::defaultCryptKey;

        $blocksize = 16;
        $encrypt_padchar = $blocksize - ( strlen( $message ) % $blocksize );
        $message .= str_repeat( chr( $encrypt_padchar ), $encrypt_padchar );

        return base64_encode(
            openssl_encrypt(
                $message,
                "aes-128-ecb",
                $key,
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
            )
        );
    }

    private function debug($name, $data){
        if(self::debug)
            $this->SendDebug($name, $data, 0);
    }


    /**
     * Check if a parent is active
     * @param $id integer InstanceID
     * @return bool
     */
    protected function HasActiveParent($id = 0)
    {
        if ($id == 0) $id = $this->InstanceID;
        $parent = $this->GetParent($id);
        if ($parent > 0) {
            $status = $this->GetInstanceStatus($parent);
            if ($status == 102) {
                return true;
            } else {
                //IPS_SetInstanceStatus($id, self::ST_NOPARENT);
                $this->debug(__FUNCTION__, "Parent not active for Instance #" . $id);
                return false;
            }
        }
        $this->debug(__FUNCTION__, "No Parent for Instance #" . $id);
        return false;
    }
    //------------------------------------------------------------------------------
    /**
     * Check if a parent for Instance $id exists
     * @param $id integer InstanceID
     * @return integer
     */
    protected function GetParent($id = 0)
    {
        $parent = 0;
        if ($id == 0) $id = $this->InstanceID;
        if (IPS_InstanceExists($id)) {
            $instance = IPS_GetInstance($id);
            $parent = $instance['ConnectionID'];
        } else {
            $this->debug(__FUNCTION__, "Instance #$id doesn't exists");
        }
        return $parent;
    }
//------------------------------------------------------------------------------
    /**
     * Retrieve instance status
     * @param int $id
     * @return mixed
     */
    protected function GetInstanceStatus($id = 0)
    {
        if ($id == 0) $id = $this->InstanceID;
        $inst = IPS_GetInstance($id);
        return $inst['InstanceStatus'];
    }
}
?>