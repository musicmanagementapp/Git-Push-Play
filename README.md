# GitPushPlay — File Structure Guide


---

## Project Root

```
Git-Push-Play/
├── app/                  ← All web code
└── README.md
```

---

## app/ Layout

```
app/
├── index.php             ← Homepage
├── login.php             ← Login
├── create-account.php    ← Registration
├── logout.php            ← Logout
├── delete_account.php    ← Delete Account
├── settings.php          ← User settings
├── artist-profile.php    ← Artist profile dashboard
├── bandpage.php          ← Band dashboard
├── schedule.php          ← Event calendar
│
├── includes/             ← Reusable PHP includes (not full pages)
│   ├── _meta.php         ← <head> meta/title tags (uses $title and $description from php)
│   ├── _header.php       ← Site header
│   ├── _footer.php       ← Site footer (not used)
│   ├── _login.php        ← Authentication — redirects to login.php if not logged in
│   └── _pullinfo.php     ← Loads all data for the logged-in user
│
├── assets/
│   ├── css/
│   │   └── style.css     ← Global stylesheet
│   │   └── page.css     ← Each page has its own stylesheet
│   ├── js/
│   │   └── main.js       ← Global JavaScript -- not used
│   ├── images/
│   │   └── favicon.ico
│   └── libs/
│       ├── auth.php      ← User functions (login, create, update, delete)
│       ├── data.php      ← CRUD functions for all JSON data
│       └── phpfunctions.php ← PHP helper functions
│
└── secure/
    ├── users.json            ← User accounts
    ├── musicians.json        ← Musician profiles
    ├── bands.json            ← Bands
    ├── band_memberships.json ← Who is in which band and their role
    ├── venues.json           ← Venue records (doesn't exist yet was part of original structure)
    ├── events.json           ← Scheduled events linking bands to venues
    └── linked_services.json  ← Spotify, YouTube, etc. (we ended up not using becuase of cURL issues) 
```

---

## Includes Reference

| File | Description |
|---|---|
| `_meta.php` | Outputs `<title>` and `<meta>` tags. Set `$title` / `$description` before including. |
| `_header.php` | Site header. |
| `_footer.php` | Site footer with links. |
| `_login.php` | Auth guard. Include at the top of any protected page — redirects if not logged in. |
| `_pullinfo.php` | Loads all logged-in user data. |

---

## Using `_pullinfo.php`

Include it on any page that needs user data (after `_login.php`):

```php
<?php
include 'includes/_login.php';
include 'includes/_pullinfo.php';
?>
```

It provides these variables:

| Variable | Contents |
|---|---|
| `$gpUser` | User record — `id`, `username`, `email`, `createdAt`, `lastLogin` |
| `$gpMusician` | Musician profile — `stageName`, `firstName`, `lastName`, `city` |
| `$gpBand` | Active band — `name`, `genre`, `formedYear`, `joinCode` |
| `$gpMembership` | User's band membership — `role`, `joinedAt` |
| `$gpBandMembers` | All active band members, each with `['musician']` attached |
| `$gpIsOwner` | `true` if the user is the band owner/admin |
| `$gpServices` | All linked streaming services |
| `$gpServiceMap` | Same, keyed by `serviceName` for direct lookup |

`$gpMusician`, `$gpBand`, and `$gpMembership` can be `null` — use `??` when reading them:

```php
$stageName = $gpMusician['stageName'] ?? 'No stage name set';
$bandName  = $gpBand['name']          ?? 'Not in a band';
```

---

## `data.php` Functions Reference

| Function | Description |
|---|---|
| `find_musician_by_user_id($userId)` | Get a musician profile by user ID |
| `create_musician($userId, $fields)` | Create a new musician profile |
| `update_musician($id, $fields)` | Update stageName, firstName, lastName, city |
| `find_band_by_id($id)` | Get a band by its ID |
| `find_band_by_join_code($code)` | Find a band by its join code |
| `create_band($ownerMusicianId, $fields)` | Create a band and join the owner |
| `update_band($id, $fields)` | Update name, genre, formedYear |
| `join_band_by_code($code, $musicianId)` | Join a band using its join code |
| `get_active_band_for_musician($musicianId)` | Get the musician's current band |
| `get_band_members($bandId)` | Get all active members with musician details attached |
| `remove_band_member($membershipId)` | Remove a member (owner cannot be removed) |
| `get_services_for_musician($musicianId)` | Get all linked services for a musician |
| `upsert_linked_service($musicianId, $serviceName, $fields)` | Add or update a streaming service link |
| `create_event($fields)` | Create a new event |
| `get_events_for_band($bandId)` | Get all events for a band |
| `create_venue($fields)` | Create a new venue |


