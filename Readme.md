# Symcon Sinclair/Gree Klimaanlagen Modul
Modul um Klimaanlagen mit Wifi Modul der Hersteller Gree zu steuern.

Unterstützte Marken:
- Sinclair
- Gree

Gree stellt auch Klimaanlagen unter anderen Firmennamen her, deshalb kann das Modul auch bei anderen Marken funktioniern. Falls das Modul bei einer anderen Anlage als den oben genannten funktioniert, bitte mitteilen, dann kann ich es in die Liste aufnehmen.


### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [Webfront](#6-webfront)
7. [PHP Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

- Ein- / Ausschalten des Gerätes
- Modus umschalten
- Soll Temperatur setzen
- Gebläsestufe und Gebläserichtung einstellen
- X-Fan ein-/ausschalten
- Ionisation ein-/ausschalten
- Displaybeleuchtung ein-/ausschalten
- Schlafmodus ein-/ausschalten
- Ecomodus ein-/ausschalten
- Auslesen der aktuellen Temperatur

Da die Innengeräte öftermal kurze "Pausen" machen habe ich eine Commandqueue implementiert d.h. falls das Gerät nicht pingbar ist wird der Befehl später gesendet. Es werden maximal 5 Kommandos in der Queue abgelegt, das Datenaktualisierungskommand jedoch nur einmal.
Falls das Gerät für die Dauer von 15*[getStatus Intervall Sek] nicht pingbar ist wird eine neue Initialisierung durchgeführt, da sich der Verschlüsselungskey geändert haben könnte.

### 2. Voraussetzungen

- IP-Symcon ab Version 5.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/emtek-at/GreeAC-Symcon`  


### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Sinclair AC Wifi'-Modul unter dem Hersteller 'Sinclair' aufgeführt.

#### 4.1 Gateway konfigurieren
__Konfigurationsseite__:

Name       | Beschreibung
---------- | ---------------------------------
Host/IP             | DNS oder IP Adresse des WLAN Moduls der Inneneinheit

#### 4.2 Instanz konfigurieren
__Konfigurationsseite__:

Name       | Beschreibung
---------- | ---------------------------------
Instanz aktivieren  | Aktiviert die Instanz und den Aktualisierungstimer
Ventilationsstufen  | Anzahl der Ventilationsstufen ohne Automodus (Anzahl der Balken auf der Fernbedienung)
Swing Links-Rechts  | Ist ein motorisierter Links <-> Rechts Luftleiter vorhanden
Frischluftventil    | Ist ein Frischluftventil vorhanden (meistens nicht)
Geräte Licht automatisch ein-/ausschalten   | schaltet das Licht am Gerät ein oder aus, je nach dem ob das Gerät selbst ein- oder ausgeschalten ist
Infos loggen        | schreibt Infos in das Symcon Log
getStatus Intervall Sek. | Aktualisierungs Intervall



### 5. Statusvariablen und Profile

Die Statusvariablen und Profile werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name               | Typ       | Beschreibung
------------------ | --------- | ----------------
Name                 | String  | Name der Inneneinheit
eingeschalten        | Boolean | Schaltet das Gerät ein/aus
Modus                | Integer | Stellt den Modus auf Auto, Kühlen, Heizen, Trocknen, Lüften
Soll Temperatur      | Integer | Setzt die Solltemperatur
Gebläsestufe         | Integer | Stellt die Gebläsegeschwindigkeit ein
Gebläserichtung      | Integer | Stellt die Lüfterrichtung und den Swingmodus ein
Gebläserichtung Links Rechts   | Integer | Falls vorhanden stellt es die Links-Rechts Lüfterrichtung ein
Ist Temperatur      | Integer | Ist Temperatur beim Gerät
Option X-Fan        | Boolean | Lüfternachlauf beim Ausschalten (nur aktiv falls gekühlt wurde)
Option Ionisieren   | Boolean | Ionisator ein-/ausschalten
Option Licht        | Boolean | Displaybeleuchtung ein-/ausschalten
Option Schlafen     | Boolean | Schlafmodus ein-/ausschalten
Option Eco          | Boolean | Ecomodus ein-/ausschalten
letzte Aktualierung | String  | Datum und Uhrzeit der letzten Aktualiesierung
MAC                 | String  | Mac Adresse des Wlan Moduls
DeviceKey           | String  | Key für die Verschlüsselung der Kommunikation. Nicht verändern! Das Gerät erzeugt den Schlüssel.

##### Profile

Nachfolgende Profile werden zusätzlich hinzugefügt und bei jedem "Änderungen übernehmen" der Instanz neu abgespeichert:

* Sinclair.DeviceMode
* Sinclair.DeviceFan3
* Sinclair.DeviceFan5
* Sinclair.DeviceFan6
* Sinclair.DeviceFan7
* Sinclair.DeviceSwinger
* Sinclair.DeviceSwingerLeRi
* Sinclair.SetTemp
* Sinclair.ActTemp

### 6. WebFront

Die Instanz wird im WebFront angezeigt. Sie können die vorhandenen Funktionen nutzen.

### 7. PHP-Befehlsreferenz

Präfix des Moduls 'SINCLAIR'

`SINCLAIR_initDevice(integer $instanceId)`

Holt den DeviceKey vom Gerät, damit Kommandos gesendet werden können. Wird automatisch aufgerufen wenn der DeviceKey leer ist.

`SINCLAIR_getStatus(integer $instanceId)`

Fragt die Daten vom Gerät ab. Diese Funktion wird vom Timer aufgerufen.

`SINCLAIR_setPower(integer $instanceId, bool $newVal)`

Schaltet das Gerät ein oder aus und setzt alle Optionen wie sie in Symcon eingestellt sind. Möchte man mehrere Variablen gleichzeitig zum Gerät schicken, können diese in IP-Symcon mit `SetValue` gesetzt werden und anschließend mit `SINCLAIR_setPower` übertragen werden. Der Vorteil ist, dass das Gerät nur einmal piepst und nicht für jede Option einzeln.

`SINCLAIR_setMode(integer $instanceId, int $newVal)`

Setzt den Modus. Mögliche Werte:
* 0: Auto
* 1: Kühlen
* 2: Trocknen
* 3: Lüften
* 4: Heizen

`SINCLAIR_setFan(integer $instanceId, int $newVal)`

Setzt die Gebläsegeschwindigkeit. Mögliche Werte je nach Konfiguration:
* 0: Auto
* 1-7: Geschwindigkeit

`SINCLAIR_setSwinger(integer $instanceId, int $newVal)`

Setzt die Gebläserichtung. Mögliche Werte je nach Konfiguration:
* 0: Stopp
* 1: Swing Oben -> Unten
* 2: Fix Oben
* 3: Fix Oben Mitte
* 4: Fix Mitte
* 5: Fix Unten Mitte
* 6: Fix Unten
* 7: Swing Mitte -> Unten 
* 8: Swing Oben Mitte -> Unten
* 9: Swing Oben Mitte -> Unten Mitte
* 10: Swing Oben -> Unten Mitte
* 11: Swing Oben -> Mitte

`SINCLAIR_setSwingerLeRi(integer $instanceId, int $newVal)`

Setzt die Gebläserichtung Links Rechts. Mögliche Werte je nach Konfiguration:
* 0: Stopp
* 1: Swing Links -> Rechts
* 2: Fix Links
* 3: Fix Links Mitte
* 4: Fix Mitte
* 5: Fix Rechts Mitte
* 6: Fix Rechts

`SINCLAIR_setTemp(integer $instanceId, int $newVal)`

Setzt die Solltemperatur.

`SINCLAIR_setOptXFan(integer $instanceId, bool $newVal)`

Schaltet den Gebläsenachlauf ein oder aus.

`SINCLAIR_setOptHealth(integer $instanceId, bool $newVal)`

Schaltet den Ionisator ein oder aus.

`SINCLAIR_setOptLight(integer $instanceId, bool $newVal)`

Schaltet die Displaybeleuchtung ein oder aus.

`SINCLAIR_setOptSleep(integer $instanceId, bool $newVal)`

Schaltet den Schlafmodus ein oder aus.

`SINCLAIR_setOptEco(integer $instanceId, bool $newVal)`

Schaltet den Ecomodus ein oder aus.

`SINCLAIR_setOptAir(integer $instanceId, bool $newVal)`

Schaltet das Frischluftventil ein oder aus.

`SINCLAIR_beep(integer $instanceId)`

Lässt das Gerät einmal piepsen.

`SINCLAIR_resetCmdQueue(integer $instanceId)`

Leert die Commandqueue falls Probleme erkannt werden.