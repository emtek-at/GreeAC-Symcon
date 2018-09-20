<?
abstract class Commands
{
    const none = -1;
    const scan = 0;
    const bind = 1;
    // etc.
}

// Klassendefinition
class sinclair extends IPSModule {

    const defaultCryptKey = 'a3K8Bx%2r8Y7#xDh';
    private $deviceMac = '';
    private $deviceName = '';
    private $deviceKey = '';

    private $actualCommand = Commands::none;

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

        $recObj = json_decode($JSONString);
        $bufferObj = json_decode($recObj->Buffer);
        $decrypted = $this->decrpyt($bufferObj->pack, $this->deviceKey);
        $decObj = json_decode($decrypted);

        $this->SendDebug('Pack decrypted', $decrypted, 0);
        $this->SendDebug('actualCommand', $this->actualCommand, 0);


        switch($this->actualCommand){
            case Commands::scan:
                $this->deviceMac = $decObj->mac;
                $this->deviceName = $decObj->name;

                SetValueString($this->GetIDForIdent('macAddress'), $this->deviceMac);
                SetValueString($this->GetIDForIdent('name'), $this->deviceName);
                $this->SendDebug('AC MAC', $decObj->mac, 0);
                $this->SendDebug('AC Name', $decObj->name, 0);
                break;
        }

        $this->actualCommand = Commands::none;
    }

    private function sendCommand($type, $cmdArr){
        if($this->actualCommand != Commands::none)
            return false;

        $this->actualCommand = $type;

        $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => json_encode($cmdArr))));

        return true;
    }


    public function test() {
        // Selbsterstellter Code
        $arr = array('t' => 'scan');
        //$r = $this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => json_encode($arr))));
        //$this->SendDebug('sdp return', $r, 0);

        $this->sendCommand(Commands::scan, $arr);
    }


    public function getStatus(){
        $this->SendDebug('getStatus', '0', 0);

        SetValueString($this->GetIDForIdent('lastUpdate'), date("Y-m-d H:i:s"));
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