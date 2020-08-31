<?php
class SysadminModule implements RSMS_Module, MessageTypeProvider {
    public const MTYPE_NOTIFY_SYSTEM_ADMIN = 'NotifySystemAdmin';

    public const NAME = 'System Admin';

    public function getModuleName(){ return self::NAME; }
    public function getUiRoot(){ return null; }
    public function isEnabled() { return false; }
    public function getActionManager(){ return null; }
    public function getActionConfig(){ return []; }

    public function getMessageTypes(){
        $mtypes = [
            new MessageTypeDto(self::NAME, self::MTYPE_NOTIFY_SYSTEM_ADMIN,
                'Email sent to System Administrator',
                Sysadmin_MessageProcessor::class,
                [Message::class]
            )
        ];

        return $mtypes;
    }

    public function getMacroResolvers(){
        $resolvers = [];
        $resolvers[] = new MacroResolver(
            Message::class,
            '[MESSAGE_JSON]', 'Sysadmin message content',
            function(Message $message){
                $json = $message->getContext_descriptor();
                $fjson = json_encode(
                    json_decode( $json ),
                    JSON_PRETTY_PRINT
                );

                return '<pre>' . $fjson . '</pre>';
            }
        );

        return $resolvers;
    }
}
?>
