# eu.businessandcode.moodlesync

**MoodleSync** is a CiviCRM extension to help you synchronize your events and event participants with Moodle.

We assume that your website and CiviCRM are the tools to promote your events and enrol your event participants.
Events, contacts, and participants will then be pushed to (synchronized with) your Moodle server.

The synchronization of contacts, events, and participants is only in 1 direction: from CiviCRM to Moodle.

The table below shows the CiviCRM terms with their corresponding Moodle terms:

| CiviCRM        | Moodle      |
|:-------------  |:----------- |
| Contact        | User        |
| Event          | Course      |
| Participant    | Enrolment   |


## Configuration in Moodle

In order to communicate with Moodle, you need to set up the web service functionality.

Login to your Moodle installation as a site administrator.

### 1. Enable Web Services

Go to:

```
Site Administration > Advanced features
```

Check "Enable web services".

### 2. Enable Web Authentication

Go to:

```
Site Administration > Plugins
```

Under Authentication click on:

```
Manage authentication
```

and enable Web services authentication.

### 3. Add an External Service

Go to:

```
Site Administration > Plugins > External Services
```

+ create user + create token + add functions

OR WRITE A MOODLE PLUGIN???

## Retrieving Course Categories

## Custom Fields

This extension creates a set of custom fields for Contacts, Events, and Participants.



## License

The extension is licensed under [AGPL-3.0](LICENSE.txt).
