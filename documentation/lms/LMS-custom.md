# LMS Custom Implementation

## Requirements

* ~~User Accounts~~ *RSMS manages users already*
* ~~Customized Content~~
* Course Assignment (based on hazards used)
* Quizzes/Scores
* Track records
* Send Renewal Notices
* Dashboard to view Training Status
* ~~Reasonable cost per-user~~
* ~~RSMS Integration~~

### Implementation Requirements

* Learning materials
  * Data Modeling
  * Management Interface
  * Storage
  * Supported File Formats
  * Assignment to Users
  * Delievery to User
* Quizzes/Tests
  * Data modeling
  * Management Interface
    * Attachment to Learning Materials
* Dashboard Interface


## Bare-Bones Learning Solution for RSMS

* Learning Materials
  * Create Training/Course record in database
    * Name
    * Description
    * Hazard(s)
    * Certification frequency
    * Upload document(s)
      * Text/HTML
      * Image
      * PDF
    * Quiz
      * Name
      * Description
      * Questions
        * Text
        * Answers
          * Text
* PI Assignment
  * Automatic assignment based on assigned Hazards and Certification Frequency
  * Email is sent when assignment is created
* My Lab
  * Add section to My Lab which Lists and Links all Assigned training
* Course Completion
  * List Course Details
  * Display training Materials
  * Navigate to Quiz (if present)
  * Store quiz responses
  * Grade quiz and store result (percentage? pass/fail?)
  * Email is sent when course is completed
