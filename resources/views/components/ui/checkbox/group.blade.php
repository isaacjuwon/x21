{{--
    Checkbox Group Wrapper Component
    
    Manages shared state for multiple checkboxes, supporting:
    - Individual checkbox selection (array of values)
    - Different visual layouts (default, pills, cards)
    - Automatic spacing based on content (descriptions, etc.)
    - Model binding synchronization (Alpine.js and Livewire)
--}}

@props([
    'variant' => 'default',
    'model' => $attributes->whereStartsWith(['wire:model', 'x-model'])
])

@php
// ====================================================================
// VARIANT-BASED LAYOUT CLASSES
// ====================================================================
$classes = match($variant) {
    // Pills: Horizontal layout with wrapping
    'pills' => 'flex gap-2 flex-wrap',
    
    // Cards: Vertical stack layout
    'cards' => 'flex flex-col gap-2',
    
    // Default: Smart vertical spacing with content-aware gaps
    default => [
        // Base spacing: 0.75rem (12px) between checkbox wrappers (except first)
        '[&>[data-slot=checkbox-wrapper]:not(:first-child)]:mt-3',
        
        // Enhanced spacing: 1rem (16px) when a checkbox with description 
        // is followed by another checkbox (provides better visual separation)
        '[&>[data-slot=checkbox-wrapper]:has([data-slot=checkbox-description])+[data-slot=checkbox-wrapper]]:mt-4'
    ]
};
@endphp

<div
    x-data="{
        // ====================================================================
        // GROUP STATE MANAGEMENT
        // ====================================================================
        /**
         * Shared state array that holds values of all checked checkboxes
         * - undefined: No model binding (checkboxes manage their own state)
         * - array: Model binding active (group manages all checkbox states)
         * 
         * Note: This property is named 'state' (not 'groupState') so child 
         * checkboxes can access it via Alpine's scope inheritance as 'this.state'
         */
        state: undefined,
        
        // ====================================================================
        // INITIALIZATION
        // ====================================================================
        /**
         * Initialize group state based on model binding presence
         */
        init() {
            this.$nextTick(() => {
                // Check if there's a model binding on the group wrapper
                const modelBinding = this.$root._x_model;
                
                if (modelBinding) {
                    // Model binding exists: initialize with bound data or empty array
                    this.state = modelBinding.get() ?? [];
                }
                // No model binding: state remains undefined
                // Individual checkboxes will manage their own state
            });
            
            // ====================================================================
            // STATE SYNCHRONIZATION WATCHER
            // ====================================================================
            /**
             * Watch for changes to group state and sync with model bindings
             * This ensures both Alpine.js and Livewire stay in sync
             */
            this.$watch('state', (newValues) => {
                // Skip sync if no model binding (prevents unnecessary operations)
                if (newValues === undefined) return;
                
                this.syncWithAlpineModel(newValues);
                this.syncWithLivewireModel(newValues);
            });
        },
        
        // ====================================================================
        // MODEL BINDING SYNCHRONIZATION
        // ====================================================================
        /**
         * Sync group state with Alpine.js model binding
         */
        syncWithAlpineModel(values) {
            this.$root?._x_model?.set(values);
        },
        
        /**
         * Sync group state with Livewire model binding
         */
        syncWithLivewireModel(values) {
            const wireModelAttribute = this.findWireModelAttribute();
            
            if (this.$wire && wireModelAttribute) {
                const propertyPath = this.$root.getAttribute(wireModelAttribute);
                const isLiveUpdate = wireModelAttribute.includes('.live');
                
                this.$wire.set(propertyPath, values, isLiveUpdate);
            }
        },
        
        /**
         * Find the wire:model attribute on the root element
         */
        findWireModelAttribute() {
            return this.$root.getAttributeNames()
                .find(attributeName => attributeName.startsWith('wire:model'));
        }
    }"
    {{-- 
        Make group state available to child checkboxes via Alpine's scope inheritance
        Child checkboxes will access this via 'this.state' 
    --}}
    {{ $attributes->class($classes) }}
    data-slot="checkbox-group"
>
    {{-- 
        CHILD CHECKBOXES
        All child checkboxes will inherit the group's Alpine scope
        and can access 'groupState' via 'this.state' property
    --}}
    {{ $slot }}
</div>