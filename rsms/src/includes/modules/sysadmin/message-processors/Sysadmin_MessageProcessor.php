<?php
class Sysadmin_MessageProcessor implements MessageTypeProcessor {
    private const DEFAULT_SYSADMIN_EMAIL = 'martina3@mailbox.sc.edu';

    private function getSysadminEmail(){
        return ApplicationConfiguration::get(
            ApplicationBootstrapper::CONFIG_SYSTEM_ADMIN_CONTACT_EMAIL,
            self::DEFAULT_SYSADMIN_EMAIL
        );
    }

    public function getRecipientsDescription(){ return "System Administrator"; }

    public function process(Message $message, $macroResolverProvider){

        // Construct macromap
        $macromap = $macroResolverProvider->resolve( [$message] );

        // Send email only to system administrator
        $sysadmin = $this->getSysadminEmail();
        $recipient_emails = [ $sysadmin ];

        // Prepare email details
        $details = [
            'recipients' => implode(',', $recipient_emails),
            'macromap' => $macromap
        ];

        // Return single-item array
        return [$details];
    }
}
?>
