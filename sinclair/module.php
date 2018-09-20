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
        $this->RegisterPropertyInteger("bindTimer", 60*10);
        $this->RegisterPropertyInteger("statusTimer", 60*2);

        $this->RegisterTimer("bind_UpdateTimer", 0, 'ETM_bind($_IPS[\'TARGET\']);');
        $this->RegisterTimer("status_UpdateTimer", 0, 'ETM_getStatus($_IPS[\'TARGET\']);');
    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        $host = $this->ReadPropertyString("host");
        $this->SendDebug('host', $host, 0);
        if (strlen($host) > 0)
        {
            //Instanz ist aktiv
            $this->SetStatus(101);

            $bindInterval = $this->ReadPropertyInteger("bindTimer");
            $this->SendDebug('Update Bind Interval', $bindInterval.' sec', 0);

            $statusInterval = $this->ReadPropertyInteger("statusTimer");
            $this->SendDebug('Update Status Interval', $statusInterval.' sec', 0);

            $this->SetTimerInterval('bind_UpdateTimer', $bindInterval*1000);
            $this->SetTimerInterval('status_UpdateTimer', $statusInterval*1000);

        }
        else
        {
            //Instanz ist aktiv
            $this->SetStatus(201);
        }
    }

    /**
     * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
     * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
     *
     * ABC_MeineErsteEigeneFunktion($id);
     *
     */
    public function MeineErsteEigeneFunktion() {
        // Selbsterstellter Code
        $this->SendDebug('testmsg', 'data3', 0);
    }

    public function bind(){
        $this->SendDebug('bind', '0', 0);
    }

    public function getStatus(){
        $this->SendDebug('getStatus', '0', 0);
    }
}
?>