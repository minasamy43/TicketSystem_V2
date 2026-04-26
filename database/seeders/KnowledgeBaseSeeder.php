<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KbCategory;
use App\Models\KbArticle;
use App\Models\KbFaq;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'getting-started',
                'title' => 'Getting Started',
                'icon' => '🚀',
                'description' => 'Learn the basics of using our support ticket system.',
                'articles' => [
                    ['title' => 'How to create your first ticket', 'slug' => 'create-ticket', 'content' => '<p>Here is how you can create your first ticket...</p>'],
                    ['title' => 'Navigating the dashboard', 'slug' => 'navigating-dashboard', 'content' => '<p>The dashboard gives you a quick overview...</p>'],
                    ['title' => 'Setting up your profile', 'slug' => 'setting-up-profile', 'content' => '<p>To set up your profile, go to settings...</p>'],
                ]
            ],
            [
                'slug' => 'account-security',
                'title' => 'Account & Security',
                'icon' => '🛡️',
                'description' => 'Manage your account settings and keep your data safe.',
                'articles' => [
                    ['title' => 'How to change your password', 'slug' => 'change-password', 'content' => '<p>Navigate to your account settings to change your password.</p>'],
                    ['title' => 'Enabling Two-Factor Authentication', 'slug' => 'enable-2fa', 'content' => '<p>2FA adds an extra layer of security...</p>'],
                    ['title' => 'Managing authorized devices', 'slug' => 'authorized-devices', 'content' => '<p>You can revoke access to devices...</p>'],
                ]
            ],
            [
                'slug' => 'technical-support',
                'title' => 'Technical Support',
                'icon' => '🛠️',
                'description' => 'Troubleshoot common technical issues and bugs.',
                'articles' => [
                    ['title' => 'Clearing browser cache', 'slug' => 'clear-cache', 'content' => '<p>Sometimes clearing cache solves loading issues...</p>'],
                    ['title' => 'Supported browsers and devices', 'slug' => 'supported-browsers', 'content' => '<p>We support Chrome, Firefox, Safari, and Edge.</p>'],
                    ['title' => 'What to do if the chat isn\'t loading', 'slug' => 'chat-not-loading', 'content' => '<p>Make sure you are connected to the internet and WebSockets are allowed.</p>'],
                ]
            ],
            [
                'slug' => 'billing-payments',
                'title' => 'Billing & Payments',
                'icon' => '💳',
                'description' => 'Questions about invoices, payments, and subscriptions.',
                'articles' => [
                    ['title' => 'How to download your invoices', 'slug' => 'download-invoices', 'content' => '<p>Go to the billing section to download your past invoices.</p>'],
                    ['title' => 'Updating your payment method', 'slug' => 'update-payment', 'content' => '<p>Use the secure payment portal to update your card details.</p>'],
                    ['title' => 'Subscription plans and pricing', 'slug' => 'subscription-plans', 'content' => '<p>View our affordable subscription tiers.</p>'],
                ]
            ],
        ];

        $faqs = [
            [
                'question' => 'How long does it take to get a response?',
                'answer' => 'Our support team typically responds within 2-4 hours during business hours.'
            ],
            [
                'question' => 'Can I reopen a closed ticket?',
                'answer' => 'Yes, you can reopen a ticket as long as it wasn\'t closed more than 7 days ago.'
            ],
            [
                'question' => 'How do I attach pictures to my ticket?',
                'answer' => 'You can drag and drop images into the chat window or use the attachment icon in the message bar.'
            ],
        ];

        foreach ($categories as $catData) {
            $cat = KbCategory::firstOrCreate(
                ['slug' => $catData['slug']],
                [
                    'title' => $catData['title'],
                    'icon' => $catData['icon'],
                    'description' => $catData['description']
                ]
            );

            foreach ($catData['articles'] as $artData) {
                KbArticle::firstOrCreate(
                    ['slug' => $artData['slug']],
                    [
                        'kb_category_id' => $cat->id,
                        'title' => $artData['title'],
                        'content' => $artData['content']
                    ]
                );
            }
        }

        foreach ($faqs as $faqData) {
            KbFaq::firstOrCreate(
                ['question' => $faqData['question']],
                ['answer' => $faqData['answer']]
            );
        }
    }
}
