# phpNetsh
An in-browser way to manage your Windows Wi-Fi profiles and an API to access limited functionality of Netsh with PHP

###The API
The API has just a few things it can do, all defined by the action .

    getProfiles
	getProfileDetails
	setProfilePassword

####getProfiles
This method requires only that the post request include the parameter 'getProfiles'. It returns a JSON object that includes the SSIDs and scopes of the profiles that a user has access to. Like this:

      {
        "Spyhouse": {
          "SSID": "Spyhouse",
          "scope": "All"
        },
        "warehousewireless": {
          "SSID": "warehousewireless",
          "scope": "All"
        },
        "canteen3255": {
          "SSID": "canteen3255",
          "scope": "Current"
        }
      }

####getProfileDetails
Requires action be set to 'getProfileDetails' and an SSID that is in the profile list. Returns a JSON object

    {
      "password": "sunshine",
      "auth": "WPA2PSK"
    }

####setProfilePassword
Requires action be set to 'setProfilePassword', an SSID that is in the profile list, and a password that is validated on the server. Returns a JSON object

    {
      "success": "Profile Spyhouse is updated on interface Wi-Fi."
    }

