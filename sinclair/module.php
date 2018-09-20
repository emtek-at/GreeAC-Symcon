<?
abstract class Commands
{
    const none = -1;
    const scan = 0;
    const bind = 1;
    const status = 2;
    // etc.
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

    const defaultCryptKey = 'a3K8Bx%2r8Y7#xDh';
    private $deviceKey = '';


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
        $this->RegisterPropertyInteger("statusTimer", 60);

        $this->RegisterVariableInteger("actualCommand", $this->Translate("varActualCommand"));
        $this->RegisterVariableString("deviceKey", $this->Translate("varDeviceKey"));

        $this->RegisterVariableString("lastUpdate", $this->Translate("varLastUpdate"));
        $this->RegisterVariableString("macAddress", $this->Translate("varMacAddress"));
        $this->RegisterVariableString("name", $this->Translate("varName"));
        $this->RegisterVariableBoolean("power", $this->Translate("varPower"));
        //mode
        //fan
        //swinger
        $this->RegisterVariableInteger("setTemp", $this->Translate("varSetTemp"));
        $this->RegisterVariableInteger("actTemp", $this->Translate("varActTemp"));
        $this->RegisterVariableBoolean("optDry", $this->Translate("varOptDry"));
        $this->RegisterVariableBoolean("optHealth", $this->Translate("varOptHealth"));
        $this->RegisterVariableBoolean("optLight", $this->Translate("varOptLight"));
        $this->RegisterVariableBoolean("optSleep", $this->Translate("varOptSleep"));
        $this->RegisterVariableBoolean("optEco", $this->Translate("varOptEco"));
        $this->RegisterVariableBoolean("optAir", $this->Translate("varOptAir"));


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
            SetValueInteger($this->GetIDForIdent('actualCommand'), Commands::none);

            $this->deviceScan();

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
        $actCmd = GetValueInteger($this->GetIDForIdent('actualCommand'));
        SetValueInteger($this->GetIDForIdent('actualCommand'), Commands::none);

        $this->SendDebug('ReceiveData', $JSONString, 0);

        $recObj = json_decode($JSONString);
        $bufferObj = json_decode($recObj->Buffer);

        $key = $actCmd < Commands::status ? self::defaultCryptKey : GetValueString($this->GetIDForIdent('deviceKey'));

        $decrypted = $this->decrpyt($bufferObj->pack, $key);
        $decObj = json_decode($decrypted);

        $this->SendDebug('Pack decrypted', $decrypted, 0);


        switch($actCmd){
            case Commands::scan:
                SetValueString($this->GetIDForIdent('macAddress'), $decObj->mac);
                SetValueString($this->GetIDForIdent('name'), $decObj->name);

                $this->SendDebug('AC MAC', $decObj->mac, 0);
                $this->SendDebug('AC Name', $decObj->name, 0);

                $this->deviceBind();
                break;
            case Commands::bind:
                SetValueString($this->GetIDForIdent('deviceKey'), $decObj->key);

                $this->SendDebug('AC DeviceKey', $decObj->key, 0);
                break;
            case Commands::status:
                $this->parseStatus($decObj->cols, $decObj->dat);

                SetValueString($this->GetIDForIdent('lastUpdate'), date("Y-m-d H:i:s"));
                break;
        }
    }

    private function sendCommand($type, $cmdArr){
        if(GetValueInteger($this->GetIDForIdent('actualCommand')) != Commands::none)
            return false;

        SetValueInteger($this->GetIDForIdent('actualCommand'), $type);

        $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => json_encode($cmdArr))));
        // starte timer und resete actual command

        return true;
    }


    public function test() {
        // Selbsterstellter Code
        $arr = array('t' => 'scan');
        //$r = $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => json_encode($arr))));
        //$this->SendDebug('sdp return', $r, 0);

        $this->sendCommand(Commands::scan, $arr);
    }




    public function deviceScan(){
        $arr = array('t' => 'scan');
        $this->sendCommand(Commands::scan, $arr);
    }
    public function deviceBind(){
        $pack = array(
            't' => 'bind',
            'uid' => 0,
            'mac' => GetValueString($this->GetIDForIdent('macAddress'))
        );
        $this->sendCommand(Commands::bind, $this->getRequest($pack, true));
    }
    public function deviceGetStatus(){
        $pack = array(
            't' => 'status',
            'mac' => GetValueString($this->GetIDForIdent('macAddress')),
            'cols' => array(DeviceParam::Power, DeviceParam::Mode, DeviceParam::SetTemperature, DeviceParam::ActTemperature, DeviceParam::Fanspeed, DeviceParam::Swinger, DeviceParam::OptAir, DeviceParam::OptDry, DeviceParam::OptEco, DeviceParam::OptHealth, DeviceParam::OptLight, DeviceParam::OptSleep1)
        );
        $this->sendCommand(Commands::status, $this->getRequest($pack, false));
    }


    private function parseStatus($cols, $dats){
        for($i=0;$i<count($cols);$i++){
            switch($cols[$i]){
                case DeviceParam::Power:
                    SetValueBoolean($this->GetIDForIdent('power'), $dats[$i]!=0 ? true : false);
                    break;
                /*case DeviceParam::Mode:
                    SetValueBoolean($this->GetIDForIdent('power'), $dats[$i]!=0 ? true : false);
                    break;
                case DeviceParam::Fanspeed:
                    SetValueBoolean($this->GetIDForIdent('power'), $dats[$i]!=0 ? true : false);
                    break;
                case DeviceParam::Swinger:
                    SetValueBoolean($this->GetIDForIdent('power'), $dats[$i]!=0 ? true : false);
                    break;*/
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
}
?>