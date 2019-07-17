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

__Konfigurationsseite__:

Name       | Beschreibung
---------- | ---------------------------------
Host/IP             | DNS oder IP Adresse des Wechselrichters
Ventilationsstufen  | Anzahl der Ventilationsstufen ohne Automodus (Anzahl der Balken auf der Fernbedienung)
Swing Links-Rechts  | Ist ein motorisierter Links <-> Rechts Luftleiter vorhanden
Frischluftventil    | Ist ein Frischluftventil vorhanden (meistens nicht)
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

Nachfolgende Profile werden zusätzlichen hinzugefügt:

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

'SINCLAIR_resetCmdQueue(integer $instanceId)'

leert die Commandqueue falls Probleme erkannt werden.
