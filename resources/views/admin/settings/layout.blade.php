<?php

use App\Livewire\Concerns\HasToast;
use App\Settings\LayoutSettings;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public ?string $banner = '';

    public ?string $about = '';

    public ?string $address = '';

    public ?string $facebook = '';

    public ?string $twitter = '';

    public ?string $instagram = '';

    public ?string $email = '';

    public string $homepageTitle = '';

    public string $homepageDescription = '';

    public string $homepageFAQTitle = '';

    public string $homepageFAQDescription = '';

    public array $homepageFAQItems = [];

    public string $homepageFeaturesTitle = '';

    public string $homepageFeaturesDescription = '';

    public array $homepageFeaturesItems = [];

    protected function rules(): array
    {
        return [
            'banner' => 'nullable|string',
            'about' => 'nullable|string',
            'address' => 'nullable|string',
            'facebook' => 'nullable|string',
            'twitter' => 'nullable|string',
            'instagram' => 'nullable|string',
            'email' => 'nullable|string',
            'homepageTitle' => 'required|string',
            'homepageDescription' => 'required|string',
            'homepageFAQTitle' => 'required|string',
            'homepageFAQDescription' => 'required|string',
            'homepageFAQItems' => 'array',
            'homepageFAQItems.*.question' => 'required|string',
            'homepageFAQItems.*.answer' => 'required|string',
            'homepageFeaturesTitle' => 'required|string',
            'homepageFeaturesDescription' => 'required|string',
            'homepageFeaturesItems' => 'array',
            'homepageFeaturesItems.*.title' => 'required|string',
            'homepageFeaturesItems.*.description' => 'required|string',
        ];
    }

    public function mount(LayoutSettings $settings): void
    {
        $this->banner = $settings->banner;
        $this->about = $settings->about;
        $this->address = $settings->address;
        $this->facebook = $settings->facebook;
        $this->twitter = $settings->twitter;
        $this->instagram = $settings->instagram;
        $this->email = $settings->email;

        $this->homepageTitle = $settings->homepage_title ?? 'Financial Freedom for Everyone';
        $this->homepageDescription = $settings->homepage_description ?? 'Manage your loans, shares, and dividends in one place. Secure, transparent, and easy to use cooperative management system.';
        $this->homepageFAQTitle = $settings->homepage_faq_title;
        $this->homepageFAQDescription = $settings->homepage_faq_description;
        $this->homepageFAQItems = $settings->homepage_faq_items;
        $this->homepageFeaturesTitle = $settings->homepage_features_title;
        $this->homepageFeaturesDescription = $settings->homepage_features_description;
        $this->homepageFeaturesItems = $settings->homepage_features_items;
    }

    public function addFAQItem(): void
    {
        $this->homepageFAQItems[] = [
            'question' => '',
            'answer' => '',
        ];
    }

    public function removeFAQItem(int $index): void
    {
        unset($this->homepageFAQItems[$index]);
        $this->homepageFAQItems = array_values($this->homepageFAQItems);
    }

    public function addFeatureItem(): void
    {
        $this->homepageFeaturesItems[] = [
            'title' => '',
            'description' => '',
        ];
    }

    public function removeFeatureItem(int $index): void
    {
        unset($this->homepageFeaturesItems[$index]);
        $this->homepageFeaturesItems = array_values($this->homepageFeaturesItems);
    }

    public function save(LayoutSettings $settings): void
    {
        $this->validate();

        $settings->banner = $this->banner;
        $settings->about = $this->about;
        $settings->address = $this->address;
        $settings->facebook = $this->facebook;
        $settings->twitter = $this->twitter;
        $settings->instagram = $this->instagram;
        $settings->email = $this->email;

        $settings->homepage_title = $this->homepageTitle;
        $settings->homepage_description = $this->homepageDescription;

        $settings->homepage_faq_title = $this->homepageFAQTitle;
        $settings->homepage_faq_description = $this->homepageFAQDescription;
        $settings->homepage_faq_items = $this->homepageFAQItems;
        $settings->homepage_features_title = $this->homepageFeaturesTitle;
        $settings->homepage_features_description = $this->homepageFeaturesDescription;
        $settings->homepage_features_items = $this->homepageFeaturesItems;
        $settings->save();

        $this->toastSuccess(__('Settings saved successfully.'));
    }

    public function render()
    {
        return $this->view()->title('Layout Settings')->layout('layouts::admin');
    }
}; ?>

<section class="w-full space-y-6">
  <x-layouts.admin.settings heading="Layout Settings" subheading="Manage your application layout settings">
  
    <div class="w-full space-y-6 mb-4">
        <x-ui.card>
            <div class="space-y-6 p-6">
                <div class="flex flex-col gap-1">
                    <x-ui.heading size="lg">General Layout Settings</x-ui.heading>
                    <x-ui.text>Manage general layout information</x-ui.text>
                </div>

                <div class="space-y-4">
                    <x-ui.input
                        wire:model="banner"
                        label="Banner URL"
                        placeholder="Enter banner image URL"
                    />

                    <x-ui.textarea
                        wire:model="about"
                        label="About Us"
                        placeholder="Enter about us text"
                        rows="3"
                    />

                    <x-ui.textarea
                        wire:model="address"
                        label="Address"
                        placeholder="Enter contact address"
                        rows="3"
                    />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="space-y-6 p-6">
                <div class="flex flex-col gap-1">
                    <x-ui.heading size="lg">Social Media Links</x-ui.heading>
                    <x-ui.text>Manage social media profile links</x-ui.text>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.input
                        wire:model="facebook"
                        label="Facebook"
                        placeholder="Enter Facebook profile URL"
                    />

                    <x-ui.input
                        wire:model="twitter"
                        label="Twitter (X)"
                        placeholder="Enter Twitter profile URL"
                    />

                    <x-ui.input
                        wire:model="instagram"
                        label="Instagram"
                        placeholder="Enter Instagram profile URL"
                    />

                    <x-ui.input
                        wire:model="email"
                        label="Email Address"
                        placeholder="Enter contact email address"
                    />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="space-y-6 p-6">
                <div class="flex flex-col gap-1">
                    <x-ui.heading size="lg">Hero Section</x-ui.heading>
                    <x-ui.text>Manage the main banner content on the homepage</x-ui.text>
                </div>

                <div class="space-y-4">
                    <x-ui.input
                        wire:model="homepageTitle"
                        label="Hero Title"
                        placeholder="Enter main heading"
                    />

                    <x-ui.textarea
                        wire:model="homepageDescription"
                        label="Hero Description"
                        placeholder="Enter main description"
                        rows="3"
                    />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="space-y-6 p-6">
                <div class="flex flex-col gap-1">
                    <x-ui.heading size="lg">FAQ Section</x-ui.heading>
                    <x-ui.text>Manage frequently asked questions displayed on the homepage</x-ui.text>
                </div>

                <div class="space-y-4">
                    <x-ui.input
                        wire:model="homepageFAQTitle"
                        label="FAQ Title"
                        placeholder="Enter FAQ section title"
                    />

                    <x-ui.textarea
                        wire:model="homepageFAQDescription"
                        label="FAQ Description"
                        placeholder="Enter FAQ section description"
                        rows="3"
                    />

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <x-ui.label>FAQ Items</x-ui.label>
                            <x-ui.button type="button" wire:click="addFAQItem" size="sm">
                                Add Question
                            </x-ui.button>
                        </div>

                        <x-ui.accordion>
                            @foreach($homepageFAQItems as $index => $item)
                                <x-ui.accordion.item wire:key="faq-item-{{ $index }}">
                                    <x-ui.accordion.trigger>
                                        {{ $item['question'] ?: 'New Question' }}
                                    </x-ui.accordion.trigger>
                                    <x-ui.accordion.content>
                                        <div class="grid gap-4 p-4">
                                            <x-ui.input
                                                wire:model="homepageFAQItems.{{ $index }}.question"
                                                label="Question"
                                                placeholder="Enter question"
                                            />
                                            <x-ui.textarea
                                                wire:model="homepageFAQItems.{{ $index }}.answer"
                                                label="Answer"
                                                placeholder="Enter answer"
                                                rows="3"
                                            />
                                            <div class="flex justify-end">
                                                <x-ui.button
                                                    type="button"
                                                    wire:click="removeFAQItem({{ $index }})"
                                                    color="danger"
                                                    size="sm"
                                                >
                                                    Remove
                                                </x-ui.button>
                                            </div>
                                        </div>
                                    </x-ui.accordion.content>
                                </x-ui.accordion.item>
                            @endforeach
                        </x-ui.accordion>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="space-y-6 p-6">
                <div class="flex flex-col gap-1">
                    <x-ui.heading size="lg">Features Section</x-ui.heading>
                    <x-ui.text>Manage the features displayed on the homepage</x-ui.text>
                </div>

                <div class="space-y-4">
                    <x-ui.input
                        wire:model="homepageFeaturesTitle"
                        label="Features Title"
                        placeholder="Enter features section title"
                    />

                    <x-ui.textarea
                        wire:model="homepageFeaturesDescription"
                        label="Features Description"
                        placeholder="Enter features section description"
                        rows="3"
                    />

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <x-ui.label>Feature Items</x-ui.label>
                            <x-ui.button type="button" wire:click="addFeatureItem" size="sm">
                                Add Feature
                            </x-ui.button>
                        </div>

                        <x-ui.accordion>
                            @foreach($homepageFeaturesItems as $index => $item)
                                <x-ui.accordion.item wire:key="feature-item-{{ $index }}">
                                    <x-ui.accordion.trigger>
                                        {{ $item['title'] ?: 'New Feature' }}
                                    </x-ui.accordion.trigger>
                                    <x-ui.accordion.content>
                                        <div class="grid gap-4 p-4">
                                            <x-ui.input
                                                wire:model="homepageFeaturesItems.{{ $index }}.title"
                                                label="Title"
                                                placeholder="Enter feature title"
                                            />
                                            <x-ui.textarea
                                                wire:model="homepageFeaturesItems.{{ $index }}.description"
                                                label="Description"
                                                placeholder="Enter feature description"
                                                rows="3"
                                            />
                                            <div class="flex justify-end">
                                                <x-ui.button
                                                    type="button"
                                                    wire:click="removeFeatureItem({{ $index }})"
                                                    color="danger"
                                                    size="sm"
                                                >
                                                    Remove
                                                </x-ui.button>
                                            </div>
                                        </div>
                                    </x-ui.accordion.content>
                                </x-ui.accordion.item>
                            @endforeach
                        </x-ui.accordion>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>

    <div class="flex justify-end">
        <x-ui.button wire:click="save" variant="primary">
            Save Layout Settings
        </x-ui.button>
    </div>
</x-layouts.admin.settings>
</section>
