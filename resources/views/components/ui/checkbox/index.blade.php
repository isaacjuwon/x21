{{-- 
    Custom checkbox component that handles both individual and group states
    Supports Livewire and Alpine.js model binding with automatic state synchronization
--}}

@aware([
    'variant' => 'default'
])

@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'value' => null,
    'checked' => false,
    'indeterminate' => false,
    'disabled' => false,
    'invalid' => false,
    'size' => 'md',
    'variant' => 'default',
    'indicator' => true,
])

<div 
    data-slot="checkbox-wrapper"
    x-data="{
        // ====================================================================
        // STATE INITIALIZATION
        // ====================================================================
        // Initialize component state from props
        _checked: @js($checked), // it's called _checked and not checked for prevent issue when the bounded property named ckecked
        value: @js($value),
        _indeterminate: @js($indeterminate),
        
        // ====================================================================
        // TOGGLE LOGIC
        // ====================================================================
        /**
         * Handles checkbox toggle interaction
         * Clears indeterminate state before toggling checked state
         */
        toggle() {
            // Clear indeterminate state when user interacts
            if (this._indeterminate) {
                this._indeterminate = false;
            }

            this._checked = !this._checked;
            this.syncHiddenInput();
            this.dispatchChangeEvent();
        },
        
        // ====================================================================
        // COMPONENT INITIALIZATION
        // ====================================================================
        /**
         * Initialize component state based on context:
         * 1. Group context: sync with group's shared state array
         * 2. Individual context: sync with model binding (Alpine/Livewire)
         */
        init() {
            this.$nextTick(()=>{
                // CASE 1: Checkbox is part of a group wrapper
                if (this.hasGroupState()) {
                    // Check if this checkbox's value exists in the group's state array
                    this._checked = this.state.includes(this.value);
                }
                // CASE 2: Standalone checkbox with model binding
                else {
                    // get initial state from Alpine model or (wire:model) bindings
                    this._checked = this.$root._x_model?.get() ?? false;
                }
            });

            this.$nextTick(()=>{
                console.log(this.$root._x_model?.get())
            })

            // ====================================================================
            // STATE SYNCHRONIZATION WATCHER
            // ====================================================================
            /**
             * Watch for changes to checked state and sync with appropriate context
             */
            this.$watch('_checked', (isChecked) => {
                // CASE 1: Group context - manage array of values
                if (this.hasGroupState()) {
                    this.syncWithGroupState(isChecked);
                }
                // CASE 2: Individual context - sync with model bindings
                else {
                    this.syncWithModelBindings(isChecked);
                }
            });           
        },

        // ====================================================================
        // GROUP STATE MANAGEMENT
        // ====================================================================
        /**
         * Determines if this checkbox is controlled by a group wrapper
         * Group state is an array when model is bound, undefined when not bound
         */
        hasGroupState() {
            return ![undefined, null].includes(this.state);
        },

        /**
         * Sync checkbox state with group's shared state array
         */
        syncWithGroupState(isChecked) {
            if (isChecked) {
                // Add value to group state if not already present
                if (!this.state.includes(this.value)) {
                    this.state.push(this.value);
                }
            } else {
                // Remove value from group state if present
                if (this.state.includes(this.value)) {
                    this.state = this.state.filter((item) => item !== this.value);
                }
            }
        },

        // ====================================================================
        // INDIVIDUAL STATE MANAGEMENT  
        // ====================================================================
        /**
         * Sync checkbox state with individual model bindings (Alpine/Livewire)
         */
        syncWithModelBindings(isChecked) {
            // Sync with Alpine.js model binding
            this.$root?._x_model?.set(isChecked);

            // Sync with Livewire model binding
            const wireModelAttribute = this.findWireModelAttribute();

            if (this.$wire && wireModelAttribute) {
            
                const propertyPath = this.$root.getAttribute(wireModelAttribute);
            
                const isLiveUpdate = wireModelAttribute.includes('.live');
            
                this.$wire.set(propertyPath, isChecked, isLiveUpdate);
            }
        },

        /**
         * Find the wire:model attribute on the root element
         */
        findWireModelAttribute() {
            return this.$root
                    .getAttributeNames()
                    .find(attributeName => attributeName.startsWith('wire:model'));
        },

        // ====================================================================
        // DOM SYNCHRONIZATION
        // ====================================================================
        /**
         * Keep hidden input in sync with component state
         * This ensures form submissions work correctly
         */
        syncHiddenInput() {
            const hiddenInput = this.$refs.hiddenInput;
            if (hiddenInput) {
                hiddenInput.checked = this._checked;
            }
        },

        /**
         * Dispatch native change event for form integration
         */
        dispatchChangeEvent() {
            this.$refs.hiddenInput?.dispatchEvent(
                new Event('change', { bubbles: true })
            );
        },
        
        // ====================================================================
        // INDETERMINATE STATE MANAGEMENT
        // ====================================================================
        /**
         * Set indeterminate state (usually called by parent components)
         * When indeterminate, the checkbox appears neither checked nor unchecked
         */
        setIndeterminate(isIndeterminate) {
            this._indeterminate = isIndeterminate;
            if (isIndeterminate) {
                this._checked = false;
            }
        }
    }"
    {{ $attributes }}
>
    {{-- 
        HIDDEN INPUT FOR FORM COMPATIBILITY
        This hidden input ensures the checkbox works with regular form submissions
        and provides a stable target for model bindings 
    --}}
    <input
        x-ref="hiddenInput"
        type="checkbox"
        @if($name) name="{{ $name }}" @endif
        @if($value !== null) value="{{ $value }}" @endif
        @if($checked) checked @endif
        @if($disabled) disabled @endif
        hidden
        tabindex="-1"
        {{ $attributes->whereStartsWith(['wire:model', 'x-model']) }}
    />
    
    {{-- 
        VISUAL CHECKBOX VARIANTS
        The actual visual representation based on variant prop 
    --}}
    @switch($variant)
        @case('pills')
            <x-ui.checkbox.variant.pill>
                {{ $slot }}
            </x-ui.checkbox.variant.pill>
            @break
            
        @case('cards')
            <x-ui.checkbox.variant.card>
                {{ $slot }}    
            </x-ui.checkbox.variant.card>
            @break
            
        @default
            <x-ui.checkbox.variant.default>                    
                {{ $slot }}
            </x-ui.checkbox.variant.default>                    
    @endswitch
</div>