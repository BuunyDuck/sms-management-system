<?php

namespace Database\Seeders;

use App\Models\ChatbotResponse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ChatbotResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            1 => ['title' => 'SkyConnect Instructions', 'file' => 'SKYCONNECT.txt'],
            2 => ['title' => 'DSL Status', 'file' => 'DSL.txt'],
            3 => ['title' => 'Cable Modem', 'file' => 'cable.txt'],
            4 => ['title' => 'Email Setup', 'file' => 'email.txt'],
            5 => ['title' => 'Outage Information', 'file' => 'outage.txt'],
            6 => ['title' => 'Speed Test', 'file' => 'speedtest.txt'],
            7 => ['title' => 'Payment Options', 'file' => 'payment.txt'],
            8 => ['title' => 'Montana Sky TV', 'file' => 'mstv.txt'],
            9 => ['title' => 'VoIP Phone Reboot', 'file' => 'voipphone.txt'],
            10 => ['title' => 'Plume WiFi', 'file' => 'plume.txt'],
            11 => ['title' => 'Fiber (GPON)', 'file' => 'fiber.txt'],
            12 => ['title' => 'Point-to-Point (PTP)', 'file' => 'p2p.txt'],
            13 => ['title' => 'IMAP Email Settings', 'file' => '13_IMAP_Settings.txt'],
            14 => ['title' => 'POP3 Email Settings', 'file' => '14_POP3_Settings.txt'],
            15 => ['title' => 'DSL Walled Garden', 'file' => '15_DSL_Walled_Garden.txt'],
            16 => ['title' => 'SkyConnect DHCP', 'file' => '16_SkyConnect_DHCP.txt'],
            17 => ['title' => 'LTE Backup', 'file' => '17_LTE.txt'],
            18 => ['title' => 'My Account Portal', 'file' => '18_MyAccount.txt'],
            19 => ['title' => 'Lost Email Recovery', 'file' => '19_Lost_Email.txt'],
            20 => ['title' => 'Forget WiFi Network', 'file' => '20_Forget_Wifi.txt'],
        ];

        foreach ($templates as $menuNumber => $data) {
            $templatePath = storage_path('chatbot/templates/' . $data['file']);
            
            if (file_exists($templatePath)) {
                $message = file_get_contents($templatePath);
                
                // Extract media URL if present
                $imagePath = null;
                if (preg_match('/<media>(.*?)<\/media>/s', $message, $matches)) {
                    // Remove media tag from message - it will be reconstructed from image_path
                    $message = preg_replace('/<media>.*?<\/media>/s', '', $message);
                    $message = trim($message);
                }
                
                ChatbotResponse::updateOrCreate(
                    ['menu_number' => $menuNumber],
                    [
                        'title' => $data['title'],
                        'message' => $message,
                        'template_file' => $data['file'],
                        'image_path' => $imagePath,
                        'active' => true,
                        'display_order' => $menuNumber,
                    ]
                );
                
                $this->command->info("✅ Imported: {$data['title']} (#{$menuNumber})");
            } else {
                $this->command->warn("⚠️  Template file not found: {$data['file']}");
            }
        }
        
        $this->command->info("\n🎉 Chatbot response seeding complete!");
    }
}

