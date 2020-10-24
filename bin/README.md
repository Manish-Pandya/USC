# RSMS Utility Scripts

The scripts contained here comprise the utilities developed to assist in development tasks for RSMS. To use, source this directory so that the scripts are available for your user.

_IMPORTANT: These scripts reference hard-coded usernames and paths and will need to be updated prior to use!_

## Overview
The main script, `rsms.sh`, acts as a passthrough to its siblings. Sibling scripts are matched by name. For example, the `help.sh` script sipmly lists the available scripts:

```
#!/bin/bash
echo "RSMS Utilities:"
ls -l /home/mmart/projects/rsms/erasmus/bin/*
```

To exeutue `help.sh` through this utility, run the following:

```
rsms help
```

Directories are interpreted as additional commands. This can be used to nest utilities, such as the following which stages a `filename.tar.gz` to the `prod` remote server:

```
rsms stage remote prod filename.tar.gz
```
