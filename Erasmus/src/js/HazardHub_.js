var app = angular.module('app', []);

app.directive('yaTree', function () {

    return {
        restrict: 'A',
        transclude: 'element',
        priority: 1000,
        terminal: true,
        compile: function (tElement, tAttrs, transclude) {

            var repeatExpr, childExpr, rootExpr, childrenExpr;

            repeatExpr = tAttrs.yaTree.match(/^(.*) in ((?:.*\.)?(.*)) at (.*)$/);
            childExpr = repeatExpr[1];
            rootExpr = repeatExpr[2];
            childrenExpr = repeatExpr[3];
            branchExpr = repeatExpr[4];

            return function link(scope, element, attrs) {

                var rootElement = element[0].parentNode,
                    cache = [];

                // Reverse lookup object to avoid re-rendering elements
                function lookup(child) {
                    var i = cache.length;
                    whil e (i--) {
                        if (cache[i].scope[childExpr] === child) {
                            return cache.splice(i, 1)[0];
                        }
                    }
                }

                scope.$watch(rootExpr, function (root) {

                    var currentCache = [];

                    // Recurse the data structure
                    (function walk(children, parentNode, parentScope, depth) {

                        var i = 0,
                            n = children.length,
                            last = n - 1,
                            cursor,
                            child,
                            cached,
                            childScope,
                            grandchildren;

                        // Iterate the children at the current level
                        for (; i < n; ++i) {

                            // We will compare the cached element to the element in 
                            // at the destination index. If it does not match, then 
                            // the cached element is being moved into this position.
                            cursor = parentNode.childNodes[i];

                            child = children[i];

                            // See if this child has been previously rendered
                            // using a reverse lookup by object reference
                            cached = lookup(child);

                            // If the parentScope no longer matches, we've moved.
                            // We'll have to transclude again so that scopes 
                            // and controllers are properly inherited
                            if (cached && cached.parentScope !== parentScope) {
                                cache.push(cached);
                                cached = null;
                            }

                            // If it has not, render a new element and prepare its scope
                            // We also cache a reference to its branch node which will
                            // be used as the parentNode in the next level of recursion
                            if (!cached) {
                                transclude(parentScope.$new(), function (clone, childScope) {

                                    childScope[childExpr] = child;

                                    cached = {
                                        scope: childScope,
                                        parentScope: parentScope,
                                        element: clone[0],
                                        branch: clone.find(branchExpr)[0]
                                    };

                                    // This had to happen during transclusion so inherited 
                                    // controllers, among other things, work properly
                                    if (!cursor) parentNode.appendChild(cached.element);
                                    else parentNode.insertBefore(cached.element, cursor);


                                });
                            } else if (cached.element !== cursor) {
                                if (!cursor) parentNode.appendChild(cached.element);
                                else parentNode.insertBefore(cached.element, cursor);

                            }

                            // Lets's set some scope values
                            childScope = cached.scope;

                            // Store the current depth on the scope in case you want 
                            // to use it (for good or evil, no judgment).
                            childScope.$depth = depth;

                            // Emulate some ng-repeat values
                            childScope.$index = i;
                            childScope.$first = (i === 0);
                            childScope.$last = (i === last);
                            childScope.$middle = !(childScope.$first || childScope.$last);

                            // Push the object onto the new cache which will replace
                            // the old cache at the end of the walk.
                            currentCache.push(cached);

                            // If the child has children of its own, recurse 'em.             
                            grandchildren = child[childrenExpr];
                            if (grandchildren && grandchildren.length) {
                                walk(grandchildren, cached.branch, childScope, depth + 1);
                            }
                        }
                    })(root, rootElement, scope, 0);

                    // Cleanup objects which have been removed.
                    // Remove DOM elements and destroy scopes to prevent memory leaks.
                    i = cache.length;

                    while (i--) {
                        cached = cache[i];
                        if (cached.scope) {
                            cached.scope.$destroy();
                        }
                        if (cached.element) {
                            cached.element.parentNode.removeChild(cached.element);
                        }
                    }

                    // Replace previous cache.
                    cache = currentCache;

                }, true);
            };
        }
    };
});


app.controller('TreeController', function ($scope, $timeout) {
  
    $scope.data = {
        children: [{label: 'Biological Materials',children: [                                           
        {label: 'Recombinant DNA', id:'1', hasChecklist: '1', children: [                                   
                {label: 'Viral Vectors', id:"4", hasChecklist: '1', children: [                         
                        {label: 'Adeno-associated Virus (AAV)', hasChecklist: '1',  id:'2',children: []}                    ,
                        {label: 'Adenovirus', id:'3', hasChecklist: '1', children: []}                  ,
                        {label: 'Baculovirus',children: []}                 ,
                        {label: 'Epstein-Barr Virus (EBV)',children: []}                    ,
                        {label: 'Herpes Simplex Virus (HSV)',children: []}                  ,
                        {label: 'Poxvirus / Vaccinia',children: []}                 ,
                        {label: 'Retrovirus / Lentivirus (EIAV)',children: []}                  ,
                        {label: 'Retrovirus / Lentivirus (FIV)',children: []}                   ,
                        {label: 'Retrovirus / Lentivirus (HIV)',children: []}                   ,
                        {label: 'Retrovirus / Lentivirus (SIV)',children: []}                   ,
                        {label: 'Retrovirus / MMLV (Amphotropic or Pseudotyped)',children: []}                  ,
                        {label: 'Retrovirus / MMLV (Ecotropic)',children: []}                   
                ]}      
            ]                   
        },                                  
        {label: 'Select Agents and Toxins',children: [                                  
                {label: 'HHS Select Agents and Toxins',children: [                          
                        {label: 'Abrin',children: []}                   ,
                        {label: 'Botulinum neurotoxins',children: []}                   ,
                        {label: 'Botulinum neurotoxin producing species of Clostridium',children: []}                   ,
                        {label: 'Cercopithecine herpesvirus 1 (Herpes B virus)',children: []}                   ,
                        {label: 'Clostridium perfringens epsilon toxin',children: []}                   ,
                        {label: 'Coccidioides posadasii/Coccidioides immitis',children: []}                 ,
                        {label: 'Conotoxins',children: []}                  ,
                        {label: 'Coxiella burnetii',children: []}                   ,
                        {label: 'Crimean-Congo haemorrhagic fever virus',children: []}                  ,
                        {label: 'Diacetoxyscirpenol',children: []}                  ,
                        {label: 'Eastern Equine Encephalitis virus',children: []}                   ,
                        {label: 'Ebola virus',children: []}                 ,
                        {label: 'Francisella tularensis',children: []}                  ,
                        {label: 'Lassa fever virus',children: []}                   ,
                        {label: 'Marburg virus',children: []}                   ,
                        {label: 'Monkeypox virus',children: []}                 ,
                        {label: 'Reconstructed 1918 Influenza virus',children: []}                  ,
                        {label: 'Ricin',children: []}                   ,
                        {label: 'Rickettsia prowazekii',children: []}                   ,
                        {label: 'Rickettsia rickettsii',children: []}                   ,
                        {label: 'Saxitoxin',children: []}                   ,
                        {label: 'Shiga-like ribosome inactivating proteins',children: []}                   ,
                        {label: 'Shigatoxin',children: []}                  ,
                        {label: 'South American Haemorrhagic Fever viruses',children: [                 
                                {label: 'Flexal',children: []}          ,
                                {label: 'Guanarito',children: []}           ,
                                {label: 'Junin',children: []}           ,
                                {label: 'Machupo',children: []}         ,
                                {label: 'Sabia',children: []}           
                        ]},                 
                        {label: 'Staphylococcal enterotoxins',children: []}                 ,
                        {label: 'T-2 toxin',children: []}                   ,
                        {label: 'Tetrodotoxin',children: []}                    ,
                        {label: 'Tick-borne encephalitis complex (flavi) viruses',children: [                   
                                {label: 'Central European Tick-borne encephalitis',children: []}            ,
                                {label: 'Far Eastern Tick-borne encephalitis',children: []}         ,
                                {label: 'Kyasanur Forest disease',children: []}         ,
                                {label: 'Omsk Hemorrhagic Fever',children: []}          ,
                                {label: 'Russian Spring and Summer encephalitis',children: []}          
                        ]},                 
                        {label: 'Variola major virus (Smallpox virus)',children: []}                    ,
                        {label: 'Variola minor virus (Alastrim)',children: []}                  ,
                        {label: 'Yersinia pestis',children: []}                 
                ]},                         
                {label: 'OVERLAP SELECT AGENTS AND TOXINS',children: [                          
                        {label: 'Bacillus anthracis',children: []}                  ,
                        {label: 'Brucella abortus',children: []}                    ,
                        {label: 'Brucella melitensis',children: []}                 ,
                        {label: 'Brucella suis',children: []}                   ,
                        {label: 'Burkholderia mallei (formerly Pseudomonas mallei)',children: []}                   ,
                        {label: 'Burkholderia pseudomallei',children: []}                   ,
                        {label: 'Hendra virus',children: []}                    ,
                        {label: 'Nipah virus',children: []}                 ,
                        {label: 'Rift Valley fever virus',children: []}                 ,
                        {label: 'Venezuelan Equine Encephalitis virus',children: []}                    
                ]},                         
                {label: 'USDA VETERINARY SERVICES (VS) SELECT AGENTS',children: [                           
                        {label: 'African horse sickness virus',children: []}                    ,
                        {label: 'African swine fever virus',children: []}                   ,
                        {label: 'Akabane virus',children: []}                   ,
                        {label: 'Avian influenza virus (highly pathogenic)',children: []}                   ,
                        {label: 'Bluetongue virus (exotic)',children: []}                   ,
                        {label: 'Bovine spongiform encephalopathy agent',children: []}                  ,
                        {label: 'Camel pox virus',children: []}                 ,
                        {label: 'Classical swine fever virus',children: []}                 ,
                        {label: 'Ehrlichia ruminantium (Heartwater)',children: []}                  ,
                        {label: 'Foot-and-mouth disease virus',children: []}                    ,
                        {label: 'Goat pox virus',children: []}                  ,
                        {label: 'Japanese encephalitis virus',children: []}                 ,
                        {label: 'Lumpy skin disease virus',children: []}                    ,
                        {label: 'Malignant catarrhal fever virus (Alcelaphine herpesvirus type 1)',children: []}                    ,
                        {label: 'Menangle virus',children: []}                  ,
                        {label: 'Mycoplasma capricolum subspecies capripneumoniae (contagious caprine pleuropneumonia)',children: []}                   ,
                        {label: 'Mycoplasma mycoides subspecies mycoides small colony (Mmm SC) (contagious bovine pleuropneumonia)',children: []}                   ,
                        {label: 'Peste des petits ruminants virus',children: []}                    ,
                        {label: 'Rinderpest virus',children: []}                    ,
                        {label: 'Sheep pox virus',children: []}                 ,
                        {label: 'Swine vesicular disease virus',children: []}                   ,
                        {label: 'Vesicular stomatitis virus (exotic): Indiana subtypes VSV-IN2, VSV-IN3',children: []}                  ,
                        {label: 'Virulent Newcastle disease virus 1',children: []}                  
                ]},                         
                {label: 'USDA PPQ SELECT AGENTS AND TOXINS',children: [                         
                        {label: 'Peronosclerospora philippinensis (Peronosclerospora sacchari)',children: []}                   ,
                        {label: 'Phoma glycinicola (formerly Pyrenochaeta glycines)',children: []}                  ,
                        {label: 'Ralstonia solanacearum race 3, biovar 2',children: []}                 ,
                        {label: 'Rathayibacter toxicus',children: []}                   ,
                        {label: 'Sclerophthora rayssiae var zeae',children: []}                 ,
                        {label: 'Synchytrium endobioticum',children: []}                    ,
                        {label: 'Xanthomonas oryzae',children: []}                  ,
                        {label: 'Xylella fastidiosa (citrus variegated chlorosis strain)',children: []}                 
                ]}                          
        ]},                                 
        {label: 'Human-derived Materials',children: [                                   
                {label: 'Blood',children: []}                   ,       
                {label: 'Fluids',children: []}                  ,       
                {label: 'Cells',children: []}                   ,       
                {label: 'Cell line',children: []}                   ,       
                {label: 'Other tissue',children: []}                            
        ]},                                 
        {label: 'Biosafety Level 1 (BSL-1)',children: []}                   ,               
        {label: 'Biosafety Level 2 (BSL-2)',children: []}                   ,               
        {label: 'Biosafety Level 2+ (BSL-2+)',children: []}                 ,               
        {label: 'Biosafety Level 3 (BSL-3)',children: []}                   ,               
        {label: 'Animal Biosafety Level 1 (ABSL-1)',children: []}                   ,               
        {label: 'Animal Biosafety Level 2 (ABSL-2)',children: []}                   ,               
        {label: 'Animal Biosafety Level 2+ (ABSL-2+)',children: []}                 ,               
        {label: 'Animal Biosafety Level 3 (ABSL-3)',children: []}                   ,               
        {label: 'Biosafety Level 1 - Plants (BL1-P)',children: []}                  ,               
        {label: 'Biosafety Level 2 - Plants (BL2-P)',children: []}                  ,               
        {label: 'Biosafety Level 3 - Plants (BL3-P)',children: []}                                  
]}]
    };

    $scope.toggleMinimized = function (child) {
        child.minimized = !child.minimized;
    };

    $scope.addChild = function (child) {
        child.children.push({
            label: '',
            children: []
        });
    };

    $scope.remove = function (child) {
        function walk(target) {
            var children = target.children,
                i;
            if (children) {
                i = children.length;
                while (i--) {
                    if (children[i] === child) {
                        return children.splice(i, 1);
                    } else {
                        walk(children[i])
                    }
                }
            }
        }
        walk($scope.data);
    }

    $scope.update = function (event, ui) {
       
        var root = event.target,
            item = ui.item,
            parent = item.parent(),
            target = (parent[0] === root) ? $scope.data : parent.scope().child,
            child = item.scope().child,
            index = item.index();

        target.children || (target.children = []);

        function walk(target, child) {
            var children = target.children,
                i;
            if (children) {
                i = children.length;
                while (i--) {
                    if (children[i] === child) {
                        return children.splice(i, 1);
                    } else {
                        walk(children[i], child);
                    }
                }
            }
        }
        walk($scope.data, child);

        target.children.splice(index, 0, child);
    };

});


app.directive('uiNestedSortable', ['$parse', function ($parse) {

    'use strict';

    var eventTypes = 'Create Start Sort Change BeforeStop Stop Update Receive Remove Over Out Activate Deactivate'.split(' ');

    return {
        restrict: 'A',
        link: function (scope, element, attrs) {

            var options = attrs.uiNestedSortable ? $parse(attrs.uiNestedSortable)() : {};

            angular.forEach(eventTypes, function (eventType) {

                var attr = attrs['uiNestedSortable' + eventType],
                    callback;

                if (attr) {
                    callback = $parse(attr);
                    options[eventType.charAt(0).toLowerCase() + eventType.substr(1)] = function (event, ui) {
                        scope.$apply(function () {

                            callback(scope, {
                                $event: event,
                                $ui: ui
                            });
                        });
                    };
                }

            });
            
            //note the item="{{child}}" attribute on line 17
            options.isAllowed = function(item, parent) {
                if (!parent) return false;
                var attrs = parent.context.attributes;
                parent = attrs.getNamedItem('item');
                attrs = item.context.attributes;
                item = attrs.getNamedItem('item');
                console.log(item, parent);
                //if ( ... ) return false;
               return true;
                };

            element.nestedSortable(options);

        }
    };
}]);