﻿////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2017 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';

class CompositionMapping {
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------

    static ONE_TO_ONE: "ONE_TO_ONE" = "ONE_TO_ONE";
    static ONE_TO_MANY: "ONE_TO_MANY" = "ONE_TO_MANY";
    static MANY_TO_MANY: "MANY_TO_MANY" = "MANY_TO_MANY";

    CompositionType: "ONE_TO_ONE" | "ONE_TO_MANY" | "MANY_TO_MANY";
    ChildType: string;
    ChildUrl: string;
    PropertyName: string;
    ChildIdProp: string;
    ParentIdProp: string;
    GerundName: string;
    GerundUrl: string;
    callGetAll: boolean;

    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------

    /**
     * Models the relationship between classes, providing URIs to fetch child objects
     *
     * Instances of this utility class should be contructed by your classes in onFullfill or later
     *
     * @param compositionType
     * @param childType
     * @param childUrl
     * @ex "getPropertyByName&type=" + this[DataStoreManager.classPropName] + "&property=rooms&id=" + this.UID
     * @param propertyName
     * @param childIdProp
     * @param parentIdProp
     * @param gerundName
     * @param gerundUrl
     */
    constructor(compositionType: "ONE_TO_ONE" | "ONE_TO_MANY" | "MANY_TO_MANY",
        childType: string,
        childUrl: string,
        propertyName: string,
        childIdProp: string,
        parentIdProp?: string,
        gerundName?: string,
        gerundUrl?: string
    ) {
        this.CompositionType = compositionType;
        this.ChildType = childType;
        this.ChildUrl = childUrl;
        this.PropertyName = propertyName;
        this.ChildIdProp = childIdProp;
        
        this.ParentIdProp = parentIdProp || DataStoreManager.uidString;

        if (this.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (!gerundName || !gerundUrl) {
                throw new Error("You must provide a gerundName and gerundUrl to fullfill this MANY TO MANY compositional relationship");
            } else {
                this.GerundName = gerundName;
                this.GerundUrl = gerundUrl;
            }
        }
    }

}

abstract class FluxCompositerBase {
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------

    static urlMapping: UrlMapping = new UrlMapping("test", "", "");

    UID: number;
    TypeName: string;
    Class: string;
    viewModelWatcher: FluxCompositerBase | any = null;

    thisClass: Function; // reference to instance's class for calling static props and methods

    private _allCompMaps: CompositionMapping[];
    get allCompMaps(): CompositionMapping[] {
        if (!this._allCompMaps) {
            this._allCompMaps = [];
            for (var instanceProp in this.thisClass) {
                if (this.thisClass[instanceProp] instanceof CompositionMapping) {
                    var cm: CompositionMapping = this.thisClass[instanceProp];
                    if (cm.ChildUrl == InstanceFactory._nameSpace[cm.ChildType].urlMapping.urlGetAll) {
                        cm.callGetAll = true; // flag that getAll will be called
                    }
                    this._allCompMaps.push(cm);
                }
            }
        }
        return this._allCompMaps;
    }

    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------

    /**
     * 
     */
    constructor() {
        if (!FluxCompositerBase.urlMapping) {
            console.log( new Error("You forgot to set URL mappings for this class. The framework can't get instances of it from the server") );
        }
        this.thisClass = (<any>this).constructor;
        this.TypeName = this.Class = (<any>this).constructor.name; // default value for TypeName
    }

    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------

    /**
     * Returns the relevant CompositionMapping for a given property.
     *
     * @param property
     */
    getCompMapFromProperty(property: string): CompositionMapping | null {
        var cms: CompositionMapping[] = this.allCompMaps;
        var l: number = cms.length;
        for (let i: number = 0; i < l; i++) {
            let cm: CompositionMapping = cms[i];
            if (cm.PropertyName == property) return cm;
        }
        return;
    }

    /**
     * Fires once instance retrieves data and fullfills dependencies.
     * Handy for overriding in child classes.
     *
     */
    onFulfill(): void {
        this.hasGetAllPermission();

        if (DataStoreManager.uidString && this[DataStoreManager.uidString]) {
            this.UID = this[DataStoreManager.uidString];
        }
        if (DataStoreManager.classPropName && this[DataStoreManager.classPropName]) {
            this.TypeName = this[DataStoreManager.classPropName];
        }
    }

    /**
     * Builds shild instances for all or a given array of CompositionMappings.
     *
     * @param compMaps
     */
    doCompose(compMaps: CompositionMapping[] | boolean): FluxCompositerBase {
        if (!compMaps) return this;

        if (Array.isArray(compMaps)) {
            // compose just properties in array...
            var len: number = (<CompositionMapping[]>compMaps).length;
            for (let i: number = 0; i < len; i++) {
                if (_.findIndex(this.allCompMaps, compMaps[i]) > -1) {
                    InstanceFactory.getChildInstances(compMaps[i], this);
                }
            }
        } else {
            // compose all compmaps...
            var len: number = this.allCompMaps.length;
            for (let i: number = 0; i < len; i++) {
                InstanceFactory.getChildInstances(this.allCompMaps[i], this);
            }
        }

        return this;
    }

    protected _hasGetAllPermission: boolean | null = null;
    /**
     * Determines if class has required permissions. Intended for overriding.
     *
     * @param evaluator
     */
    hasGetAllPermission(evaluator: Function | boolean = false): boolean {
        if (this._hasGetAllPermission == null) {
            this._hasGetAllPermission = (typeof evaluator == "function") ? evaluator() : evaluator;
        }

        return this._hasGetAllPermission;
    }

    /**
     * Dynamically constructs a class' CompositionMapping instance's childUrl based on this instance's properties.
     *
     * @param cm
     */
    getChildUrl(cm: CompositionMapping): string {
        var pattern: RegExp = /\{\{\s*([a-zA-Z_\-&\$\[\]][a-zA-Z0-9_\-&\$\[\]\.]*)\s*\}\}/g;
        let str = cm.ChildUrl.replace(pattern, (sub: string): string => {
            sub = sub.match(/\{\{(.*)\}\}/)[1];
            var parts: string[] = sub.split(".");
            sub = parts[0];
            for (var n: number = 1; n < parts.length; n++) {
                sub += "['" + parts[n] + "']";
            }
            if (parts.length > 1) {
                sub = eval(sub);
            }

            return this[sub];
        });
        
        return str;
    }

}