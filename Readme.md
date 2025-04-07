# View-Statistics Extension for TYPO3

This extension inserts statistics records on each page. It doesn't use any cookies!


>   #### Attention: {.alert .alert-danger}
>   
>   This extension doesn't track anything if you log in as a backend user and access the frontend simultaneously with the same domain name. In this case open another browser as a frontend user in order to trigger tracking. Even an incognito window of the same browser might prevent tracking.


**Features:**

*   Select data to be tracked (configurable in Extensionmanager: all visitors - both logged-in and non-logged-in frontend users, only frontend users who are logged in or only visitors who have not logged in)
*   Optional: Track the ID of the frontend user
*   Optional: Track IP address
*   Optional: Track login duration of frontend users
*   Optional: Track User Agents (for example which web browser was in use)
*   Backend module with: overview, listings, CSV export and user permissions
*   Track pages and objects such as: news articles (EXT:news), downloads (EXT:downloadmanager), products (EXT:shop), realt properties (EXT:openimmo)
*   Configure your own objects in TypoScript
