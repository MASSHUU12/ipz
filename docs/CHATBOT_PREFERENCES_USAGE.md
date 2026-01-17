# Chatbot Preferences Usage Guide

This guide explains how logged-in users can change their preferences by talking with the chatbot.

## Available Preference Commands

### 1. View Current Preferences

Show all your current preference settings:

```
show my preferences
list my settings
view my preferences
```

**Example Response:**
```
Here are your current preferences:
- City: **Warsaw**
- Notification Method: **E-mail**

**Warning Subscriptions:**
- Meteorological: ✅ Enabled
- Hydrological: ❌ Disabled
- Air Quality: ✅ Enabled
- Temperature: ✅ Enabled (Threshold: **25.0°C**)
```

### 2. Change City

Update your city preference:

```
set my city to Warsaw
change my location to Krakow
update my city to Gdansk
```

**Example Response:**
```
✅ Your city has been updated to **Warsaw**.
```

### 3. Change Notification Method

Set how you want to receive notifications:

```
set my notification method to SMS
change my notification method to email
update my notice method to both
```

**Available options:**
- `SMS` - Receive notifications via SMS
- `E-mail` or `email` - Receive notifications via email
- `Both` - Receive notifications via both SMS and email

**Example Response:**
```
✅ Your notification method has been updated to **Both**.
```

### 4. Enable/Disable Warning Subscriptions

Control which types of warnings you want to receive:

**Meteorological Warnings:**
```
enable meteorological warnings
disable meteorological warnings
turn on meteorological warnings
turn off meteorological warnings
```

**Hydrological Warnings:**
```
enable hydrological warnings
disable hydrological warnings
```

**Air Quality Warnings:**
```
enable air quality warnings
disable air quality warnings
activate air quality warnings
deactivate air quality warnings
```

**Example Response:**
```
✅ Meteorological warnings have been **enabled**.
```

### 5. Temperature Warnings

**Enable temperature warnings (with optional threshold):**
```
enable temperature warnings
enable temperature warnings at 25
enable temperature warnings with 30.5
turn on temperature warnings to 22
```

**Disable temperature warnings:**
```
disable temperature warnings
turn off temperature warnings
```

**Set temperature threshold only:**
```
set temperature threshold to 25
change temperature limit to 30.5
update temperature threshold to 22
```

**Example Responses:**
```
✅ Temperature warnings have been **enabled** with a threshold of **25.5°C**.
✅ Temperature warnings have been **disabled**.
✅ Temperature threshold has been updated to **30.0°C**. Temperature warnings are currently **enabled**.
```

## Authentication Requirement

**Important:** All preference change commands require you to be logged in. If you're not logged in, the chatbot will respond:

```
I'm sorry, you need to be logged in to change your preferences.
```

## Natural Language Support

The chatbot supports various natural language patterns for each command. You can use:
- `set`, `change`, or `update` for modification commands
- `enable`, `disable`, `turn on`, `turn off`, `activate`, or `deactivate` for toggle commands
- Optional words like `my` in most commands

## First Time Setup

If you haven't set up any preferences yet, you can start by setting your city:

```
set my city to Warsaw
```

The system will create your preference profile automatically.

## Tips

1. **Be specific** - Use the exact wording patterns shown in the examples for best results
2. **Check your preferences** - Use `show my preferences` to verify your changes
3. **Temperature thresholds** - You can use decimal values (e.g., 25.5) for temperature thresholds
4. **Case insensitive** - Commands work with any capitalization (e.g., "sms" or "SMS")
