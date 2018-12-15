<?php

class MacroResolverProvider {

    public static function build( $messageTypes ){
        $LOG = Logger::getLogger(__CLASS__);

        if( !is_array($messageTypes) ){
            $messageTypes = array($messageTypes);
        }

        $LOG->debug("Build MacroResolverProvider for " . count($messageTypes) . ' message types');
        $resolvers = array();

        foreach($messageTypes as $messageType){
            // Get the module for this type
            $module = ModuleManager::getModuleByName($messageType->getModule());

            if( $module instanceof MessageTypeProvider ){
                $LOG->debug("Get MacroResolvers from " . $module->getModuleName());
                // Get all resolvers from Module
                // Filter to just this message type's contexts
                foreach($module->getMacroResolvers() as $resolver){
                    if( $resolver->class == null || in_array($resolver->class, $messageType->contextTypes) ){
                        // Resolver is appropriate for this message; add to array
                        $resolvers[] = $resolver;
                    }
                }
            }
            else {
                // Module is not a message provider...
                throw new Exception( $messageType->getModule() . " is not a MessageTypeProvider");
            }
        }

        $LOG->debug("Building MacroResolverProvider with " . count($resolvers) . ' resolvers');
        $provider = new MacroResolverProvider( $resolvers );
        $LOG->debug("Built MacroResolverProvider with " . count($provider->resolvers) . ' resolvers');
        return $provider;
    }

    private $resolvers;

    public function __construct( $resolvers ){
        $this->resolvers = $resolvers;
    }

    public function resolve( $contexts ){
        if( !is_array($contexts) ){
            $contexts = array($contexts);
        }

        $macros = array();
        // Resolve each context
        foreach($contexts as $context ){
            $context_type = get_class($context);
            foreach( $this->resolvers as $resolver ){
                // Match context class to resolver's
                if( $resolver->class == $context_type ){
                    $macros[$resolver->key] = $resolver->resolve($context);
                }
            }
        }

        // Resolve constant Macros
        foreach( $this->resolvers as $resolver ){
            if( $resolver->class == null ){
                $macros[$resolver->key] = $resolver->resolve(null);
            }
        }

        return $macros;
    }

    public function resolveDescriptions(){
        $macros = array();
        foreach( $this->resolvers as $resolver ){
            $macros[] = new MacroDto($resolver->key, $resolver->describe());
        }

        return $macros;
    }
}

?>