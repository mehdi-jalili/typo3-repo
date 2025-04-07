# Configure the View Statistics extension

>   #### Notice: {.alert .alert-warning}
>
> Diese Erweiterung trackt nicht, wenn Sie gleichzeitig mit einem Backend-Benutzer angemeldet sind
> und das Frontend mit demselben Domainnamen aufrufen. Verwenden Sie in diesem Fall einen anderen Browser für den
> Aufruf des Frontends, um das Tracking auszulösen! Selbst ein Inkognito-Fenster des gleichen Browsers kann u. U.
> verhindern, dass das Tracking ausgeführt wird.

## General configuration

The following global settings are made in the extension settings in extension manager:

*   **Who should be tracked?**
    This setting defines tracking behavior. Possible options are:
    *   **nonLoggedInOnly**
        Only page views from users who are not logged in are tracked.
    *   **loggedInOnly**
        Only page views from loggedin users are tracked.
    *   **all**
        All page views are tracked, regardless of whether the user is logged in or not.
*   **Track frontend user ID?**
    If this is checked, each tracking data record saves the id of the logged-in frontend user who triggered it.
*   **Track IP address?**
    If this is checked, the requesting IP is saved in each tracking data record.
*   **Track user agent?**
    If this is checked, the user agent (eg. browser) is saved.
*   **Track login duration?**
    If this is checked, how long the frontend user is logged in.
