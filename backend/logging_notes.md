# Logging Notes
What do we log?
When do we log it?
How do we log (store) it?

- Start a new session when the game starts
- Log a new scene when it starts
- Don't log a new scene when it restarts (?)
- All loggables post to the logger when it wants to be logged
- The logger posts the loggable to the server
- Anthing can also post an entry to the logger, such as an event
- Stop a session when the application quits

## What should be logged?
### General
- Player
    - Position X
    - Rotation X
    - Events
        - Attacking X
        - Attacked (X)
        - Health change
- Wolf Scenes
    - All
        - Player seen X
        - Player heard
        - Player attacked
        - Wolf died X
    - Trap
        - Placed
        - Pickup
        - Kill
    - Kill
    - Sneak



## Log many things at the same time
- Receive an array of entries
    - Each entry has
        - session_id
        - event
        - gameobjects (array)
            - position (vector3)
            - rotation (quaternion)
            - name
            - instance_id
        - float_data
        - string_data
        - int_data
        - vector3_data
        - quaternion_data

## Enemies
- Log
    - Position
    - Rotation
    - Health
    - State
    - Attacks
        - Success?

## Globals
- Log
    - Session
    - Session start
    - Session end
    - List all loggables (game objects, with ID etc.)

# DB Design

## sessions
- id [uint] (AI)
- app_version [uint]
- start [timestamp]
- end [timestamp]

## scenes
- id [uint] (AI)
- session_id [uint] <- sessions.id
- name [varchar]
- start [timestamp]
- end [timestamp]

## loggables
- id [uint] (AI)
- scene_id [uint] <- scenes.id
- name [varchar]

## entries
- id [uint] (AI)
- scene_id [uint] <- scenes.id
- event [varchar]
- loggable_id [uint] <- loggables.id
- timestamp [timestamp]

## metadata

### string_data
- id [uint] (AI)
- entry_id <- entries.id
- gameobject_id <- gameobjects.id
- key [varchar]
- value [varchar]

### int_data
- id [uint] (AI)
- entry_id <- entries.id
- gameobject_id <- gameobjects.id
- key [varchar]
- value [int]

### float_data
- id [uint] (AI)
- entry_id <- entries.id
- gameobject_id <- gameobjects.id
- key [varchar]
- value [float]

## vector3
- id [uint] (AI)
- entry_id <- entries.id
- gameobject_id <- gameobjects.id
- key []
- x [float]
- y [float]
- z [float]

## quaternion
- id [uint] (AI)
- entry_id <- entries.id
- gameobject_id <- gameobjects.id
- key []
- x [float]
- y [float]
- z [float]
- w [float]