# ride-app-filtered-log

This modules allows you to create a filtered log from your properties.

## Options

You can specify some options in parameters.json, which will alter the behaviour of the log.

### log.levels

This parameter takes an array of log levels.

Available levels are:
- "E" for error
- "I" for information
- "W" for warning
- "D" for debug

Example:
```js
// parameters.json
{
    // ...

    "log.levels" : ["E", "I", "D", "W"]
}
```

### log.fields

This parameter takes an array of fields which can be showed in your log messages. This order of the fields will determine the order in which they appear in log messages.

Available fields are:
- "id"
-"date"
-"client"
-"duration"
-"source"
-"memory"
-"level"
-"title"
-"description"

Example:
```js
// parameters.json
{
    // ...

    "log.fields" : ["duration", "source", "title", "description"]
}
```

### log.sources

This parameter takes an array of sources. Examples are "app" or "database".

Example:
```js
// parameters.json
{
    // ...

    "log.sources" : ["app", "controller"]
}
```

### log.separator

A separator can be specified to override the default column separator. This parameter requires a string value.

Example:
```js
// parameters.json
{
    // ...

    "log.separator" : "::"
}
```

### log.colors

By enabling this option, the log will be colored. This parameter requires a boolean value.

Example:
```js
// parameters.json
{
    // ...

    "log.colors" : true
}
```

### log.file

Specify a different log file by providing a path.

Example:
```js
// parameters.json
{
    // ...

    "log.file": "/sites/ridme/htdocs/debug.log",
}
```
