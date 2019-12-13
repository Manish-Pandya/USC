# Learning Management System Research

I've been looking at LMS options between tasks over the past few days, and have been using the following as requirements:

* User Accounts
* Customized Content
* Course Assignment (based on hazards used)
* Quizzes/Scores
* Track records
* Send Renewal Notices
* Dashboard to view Training Status
* Reasonable cost per-user
* RSMS Integration

Based on my findings of the 3 solutions we talked about, I would recommend reviewing SCORM Cloud more closely. If it meets your training needs, then I believe it would provide the most flexibility and least ongoing cost. They also provide a persistent free-trial (limited only by number of Registrations and size of course files), so it could also be used to explore a proof-of-concept integration without fully committing to it.

Let me know if there's anything more specific you'd like me to take a look at with these or others; there are many, many, more LMS options that we could explore.

## Solutions

### [TrainCaster](https://www.traincaster.com/lms_overview.shtml)

Just as [Mark] explained over Thanksgiving, there is not a lot of detail to be found without contacting TrainCaster directly for a quote and/or demo (though they do have some additional FAQ/Help details which can be found in their Support portal). The key point that stood out to me is that they do not seem to have API support for their tool, meaning that integration with RSMS is not likely possible. Otherwise, this is a well-known and feature-full LMS.

| Requirement | Notes |
|:---|:---|
| User Accounts | User are fully managed by the TrainCaster application. |
| Customized Content | Courses and Curriculums are fully customizable, and support a wide range of formats (including PowerPoint documents and SCORM packages) |
| Course Assignment (based on hazards used) | Assignments are made to Users through TrainCaster |
| Quizzes/Scores | [Quizzes and Exams](https://www.traincaster.com/help/documents/courseManager/quizzes.shtml) can be created and added to Courses through TrainCaster. |
| Track records | Reports can be generated by TrainCaster and have 3 broad categories: Trainee, Compliance, and Administrative reports. Report generation can also be automated. |
| Send Renewal Notices | Compliance can be managed and reminders are automatically sent. |
| Dashboard to view Training Status | 'My TrainCaster' displays a User's assignments, status, due dates, history, etc. |
| Reasonable cost per-user | Contact TrainCaster for Demo & Quote |
| RSMS Integration | No published developer API; integration with RSMS is unlikely |

### [SAP Litmos](https://www.litmos.com/)

Litmos is another widely known LMS and does supply a complete developer API for custom integrations. They also advertise their pricing scheme, which is based on a maximum number of active users.

| Requirement | Notes |
|:---|:---|
| User Accounts | User are fully managed by the Litmos application. Users can also be grouped into Teams. |
| Customized Content | Modules can be created via Litmos' application, or by uploading SCORM- or xAPI-compliant packages, PowerPoint presentations, Flash files, or Audio files |
| Course Assignment (based on hazards used) | Assignments are made to Users or Teams through Litmos or their API. Hazard-based Assignments can be accomplished via RSMS integration using their API. We would need to strategize about how to associate courses with Hazards |
| Quizzes/Scores | Quizzes and Exams are available in Litmos' [Assessments](https://support.litmos.com/hc/en-us/articles/227737607-Assessment-Module-Overview-) module |
| Track records | Customizable Reports are available through Litmos |
| Send Renewal Notices | Courses can be given [Compliance](https://support.litmos.com/hc/en-us/articles/227742447-Compliance-Due-Dates-and-Course-Expiration) status and will automatically track Users' compliance, reassigning the courses automatically after a configurable period. |
| Dashboard to view Training Status | Users can log in to view their current and past trainings. Additionally, user training details can be retrieved and displayed by RSMS using integration with their API. |
| Reasonable cost per-user | Pricing is based on blocks of Active users. Minimum cost of 150 active users @ $6 per user per month, billed annually ($900/month) |
| RSMS Integration | Full-featured API support |

### [SCORM Cloud](https://rusticisoftware.com/products/scorm-cloud/)

SCORM Cloud is less feature-full than TrainCaster and Litmos, but its price is structured more closely to your use-case: an account is billed monthly for a maximum number of Registrations (defined as a Learner taking a Course) rather than for a static number of users. E.g. if you have 100 users and only 5 of those users take one course each in a month, that amounts to only 5 Registrations for that month. Additionally, they do provide a developer API which would allow for RSMS integration.

| Requirement  | Notes |
|:---|:---|
| User Accounts | Users are managed through SCORM Cloud, can be implicitly created by Registering a person for a Course, and can be created via their API. |
| Customized Content | Courses can be uploaded so long as they are formatted to be compliant with either `SCORM`, `xAPI`, `AICC`, or `cmi5` formats. Note that Adobe Captivate can be used to publish `SCORM`-compliant packages. Additionally, SCORM Cloud provides a video conversion tool to process existing videos into `SCORM`-compliant packages.|
| Course Assignment (based on hazards used) | Courses are assigned by inviting Learners (either publicly by providing a Link, privately by email, or registered on-the-fly by the API). Hazard-based Assignments can be accomplished via RSMS integration using their API. We would need to strategize about how to associate courses with Hazards |
| Quizzes/Scores | Quizzes can be created using the SCORM Cloud 'Quizzage' web application within the SCORM Cloud dashboard |
| Track records | Reporting tools are available using the SCORM Cloud 'Reportage' web application. |
| Send Renewal Notices | SCORM Cloud does not track Compliance features or support managed renewals. This would need to be managed by RSMS Integration |
| Dashboard to view Training Status | SCORM Cloud provides a dashboard for users to log in view their training. Additionally, user training details can be retrieved and displayed by RSMS using integration with their API. |
| Reasonable cost per-user | Accounts are billed Monthly, pricing tiers define a maximum number of Registrations per month, with additional cost per-registration over the limit. |
| RSMS Integration | Full-featured API support |

### [Moodle](https://www.moodle.com/)

Moodle is a Free, Open-Source LMS.

| Requirement  | Notes |
|:---|:---|
| User Accounts | Users are managed by Moodle, and can be created via their API |
| Customized Content | Moodle supports a variety of content formats out-of-the-box, and provides a Plugin interface for extending functionality further |
| Course Assignment (based on hazards used) | Users are assigned courses by being Enrolled in them (either through the system or by self-enrollment). Hazard-based Assignments can be accomplished via RSMS integration using their API. We would need to strategize about how to associate courses with Hazards. |
| Quizzes/Scores | Quizzes can be created as part of courses |
| Track records | Reports can be generated by user, course, or various other vectors. |
| Send Renewal Notices | Re-enrollments are not supported. A custom solution would be required to support renewals |
| Dashboard to view Training Status | A user dashboard is provided, and training/enrollment data can be retrieved and displayed by RSMS using integration with their API. |
| Reasonable cost per-user | Moodle is Free software under the GNU General Public License |
| RSMS Integration | Full-featured API support |

https://moodle.org/plugins/local_recompletion