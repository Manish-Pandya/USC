# Institutional Biosafety Committee (IBC) Module

## User Roles
IBC Module will make use of existing roles:
* Admin
* Principal Investigator

Adds new roles:
* `IBC Chair`
* `IBC Member`

## Features

### IBC Administration [`Admin`]
Allows administrative users to oversee the full lifecycle of all IBC Protocols across all PIs.

Existing protocols are displayed in the following distinct categories (based on Protocl Status). In all tables, links are provided to view a Protocol.

> **A note regarding the Protocol Workflows**
>
> By design, the IBC Module for RSMS does not force a particular workflow to be followed as the correct path varies between Hazards. It is up to the Administrator to ensure that a protocol follows the correct path.

##### Not Submitted
Protocols which have not yet been submitted are detailed here. Non-submitted Protocols may be Inactivated.

##### Submitted
Protocols which been submitted are detailed here.

##### Protocols In Review
Protocols which are in-review are detailed here.

##### Returned for Revisions
Protocols which must be revised are detailed here. 

##### Approved
Protocols which been Approved are detailed here.

#### Protocol Review Assignment
Protocols which have been Submitted may be assigned for review.

#### Protocol Review Assignment - Full IBC Review
Protocols which have been Submitted may be assigned for a full IBC review.

#### Protocol Management
Manages a single Protocol. See `View Protocol Details [Principal Investigator]` for general details of the Protocol Detail view.

#### Section / Question Management [`Admin`]
Manages all Sections, Questions, and Answers which are used to generate Protocols

##### Top-Level Section Management
A Section groups a set of Questions together. A 'Top-Level' Section is mapped directly to a Hazard.
When a Protocol is viewed, all Sections relevant to the Protocol's Hazard are displayed.

##### Question Management
Manage Questions within a Section.

###### Question Aspects

| Aspect | Description |
| :--- | :--- |
| Question Text | Content of the question |
| Answer Type | Type of answers to display (see table below) |
| Possible Answers | List of possible answers which can be selected |

###### Answer Types

| Answer Type | Description |
| :--- | :--- |
| Multiple Choice | Many possible answers are given, but only one can be chosen |
| Multi Select | Many possible answers are given, and one or more can be chosen |
| Table | All answers must be provided in separate text fields |
| Free Text | Answer is a text area |

###### Sub-Level Sections
A Section can also be mapped to a Possible Answer of a Question.
When a Protocol is viewed and a Possible Answer which contains one or more Sub-Sections is selected,
those Sub-Sections are also displayed in the Protocol

###### Special Dynamic Forms
An Answer can also include a special form which displays a dynamic list of options for the user to select:
* BioSafety Cabinets
* ...More TBD

### Email Management [`Admin`]
Allows Administrative users to manage Email Teamplates which are used to generate and send Emails to involved parties.

#### Create / Edit email template
An Email Template is linked to a Protocol Status. When any Protocol is transitioned to a Status which has any linked Email Templates,
an Email is generated and sent to specific users.

Email Template consists of a standard email Subject and Body.

##### Interpolation Placeholders
Special placeholders can be added to an Email Template's subject or body. Each placeholder will be replaced with content relevant to the email's context, such as a Protocol.

Available Placeholders:

| Placeholder | Notes |
| :--- | :--- |
| [PI] | Principal Investigator name |
| [Protocol Title] | |
| [Protocol Number] | |
| [Protocol Approval Date] | |
| [Expiration Date] | |
| [Reference Number] | |
| [Review Assignment Name] | |
| [Review Assignment Due Date] | |
| [Meeting Date] | |
| [Location] | |

### Meeting Management [Admin]
Allows Administrative users to manage and schedule IBC Meetings.

A Meeting consists of the following aspects:

###### Date / Time
Date and Time the meeting is to take place

###### Location
Room where the meeting is to take place

###### Agenda
Text representing the meeting's Agenda

###### Attendees
List of Users which are included in the meeting. Users are selected from a subset of RSMS users who belong to any of the IBC Roles.

### Protocol Management [`Principal Investigator`]
Allows review of existing and creation of new Protocols for the current PI.

#### Prepare a New Protocol
User may prepare a new Protocol, providing the following data:

###### Protocol Title

###### Protocol Type
Type is based on specific Biological Hazards and provides the basis of the Protocol form which the PI will copmlete.

###### Principal Investigator(s)
List of PIs to which the protocol pertains. By default, the current PI is selected.

#### Existing Protocols
Existing protocols are displayed in the following distinct categories (based on Protocl Status).

See `View Protocol Details [Principal Investigator]` for details on Viewing and Completing Protocol details.

##### Not Submitted
Protocols which have not yet been submitted are detailed here. Links are provided to either View (and complete) or Inactivate the protocol.

##### Submitted
Protocols which been submitted are detailed here. Links are provided to either View (and complete) or Inactivate the protocol.

##### Returned for Revisions
Protocols which must be revised are detailed here. 

##### Approved
Protocols which been Approved are detailed here.

### View Protocol Details [`Principal Investigator`, `Admin`]
Details a Protocol's questions, by Section, allows the User to enter responses and comments, and provides controls for transitioning the Protocol between Statuses.

The Sections included in a protocol are based primarily on the Hazard to which the Protocol pertains. Additional Sections may be included based on specific Answers which were selected.

### Protocol Reviews and Meetings [`IBC Member`]
Allows review of assigned Protocols and Meetings for the current `IBC Member`.

#### Protocol Reviews
Table view of all Protocols which are `In Review` and have the current `IBC Member` listed as a Reviewer.

#### Committee Meetings
Table view of all Meetings scheduled for the current Year.
