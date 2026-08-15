<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ==========================================
            // 1. WHOLE BIKES
            // ==========================================
            // Mountain Bikes
            [
                'ProductName' => 'Trek Marlin 7 Gen 3 Hardtail MTB',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Mountain Bikes',
                'Price' => 38500.00,
                'Stocks' => 12,
            ],
            [
                'ProductName' => 'Giant Talon 1 29er MTB',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Mountain Bikes',
                'Price' => 29500.00,
                'Stocks' => 8,
            ],

            // Road Bikes
            [
                'ProductName' => 'Specialized Allez E5 Disc Road Bike',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Road Bikes',
                'Price' => 54000.00,
                'Stocks' => 5,
            ],
            [
                'ProductName' => 'Cannondale CAAD13 Shimano 105',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Road Bikes',
                'Price' => 85000.00,
                'Stocks' => 3,
            ],

            // Fixie Bikes
            [
                'ProductName' => 'State Bicycle Co. Core-Line Fixie',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Fixie Bikes',
                'Price' => 18500.00,
                'Stocks' => 10,
            ],
            [
                'ProductName' => 'Tsunami SNM100 Track Fixed Gear',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Fixie Bikes',
                'Price' => 14200.00,
                'Stocks' => 15,
            ],

            // Gravel Bikes
            [
                'ProductName' => 'Giant Revolt 2 Gravel Bike',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Gravel Bikes',
                'Price' => 48000.00,
                'Stocks' => 6,
            ],
            [
                'ProductName' => 'Marin Gestalt 1 All-Road Bike',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Gravel Bikes',
                'Price' => 42500.00,
                'Stocks' => 7,
            ],

            // Kids Bikes
            [
                'ProductName' => 'RoyalBaby Freestyle 16-Inch Kids Bike',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Kids Bikes',
                'Price' => 6500.00,
                'Stocks' => 20,
            ],
            [
                'ProductName' => 'Strider 14x Sport Balance Bike',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Kids Bikes',
                'Price' => 8900.00,
                'Stocks' => 14,
            ],

            // Folding Bikes
            [
                'ProductName' => 'Dahon Mariner D8 Folding Bike',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Folding Bikes',
                'Price' => 27500.00,
                'Stocks' => 9,
            ],
            [
                'ProductName' => 'Tern Link C8 Folding Bicycle',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Folding Bikes',
                'Price' => 24000.00,
                'Stocks' => 11,
            ],

            // BMX Bikes
            [
                'ProductName' => 'Mongoose Legion L100 Freestyle BMX',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'BMX Bikes',
                'Price' => 16500.00,
                'Stocks' => 8,
            ],
            [
                'ProductName' => 'Sunday Primer 20-Inch BMX',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'BMX Bikes',
                'Price' => 19800.00,
                'Stocks' => 6,
            ],

            // Electric Bikes
            [
                'ProductName' => 'Specialized Turbo Vado 3.0 e-Bike',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Electric Bikes',
                'Price' => 145000.00,
                'Stocks' => 2,
            ],
            [
                'ProductName' => 'Fiido D4s Folding Electric Bike',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Electric Bikes',
                'Price' => 32000.00,
                'Stocks' => 4,
            ],

            // Fat Bikes
            [
                'ProductName' => 'Mongoose Dolomite 26-Inch Fat Tire Bike',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Fat Bikes',
                'Price' => 21000.00,
                'Stocks' => 5,
            ],
            [
                'ProductName' => 'Silverback Scoop Delight Fat Bike',
                'Category' => 'Whole Bikes',
                'SubCategory' => 'Fat Bikes',
                'Price' => 36500.00,
                'Stocks' => 3,
            ],


            // ==========================================
            // 2. BIKE PARTS
            // ==========================================
            // Framesets
            [
                'ProductName' => 'Twitter Overlord Carbon MTB Frame',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Framesets',
                'Price' => 18500.00,
                'Stocks' => 10,
            ],
            [
                'ProductName' => 'Tsunami Track Alloy Frameset w/ Carbon Fork',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Framesets',
                'Price' => 9800.00,
                'Stocks' => 12,
            ],

            // Cockpit
            [
                'ProductName' => 'Uno Kalloy 31.8mm Lightweight Stem',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Cockpit',
                'Price' => 850.00,
                'Stocks' => 25,
            ],
            [
                'ProductName' => 'Zipp Service Course SL Drop Handlebar',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Cockpit',
                'Price' => 4200.00,
                'Stocks' => 15,
            ],

            // Wheelsets
            [
                'ProductName' => 'Shimano RS100 700c Road Wheelset',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Wheelsets',
                'Price' => 6800.00,
                'Stocks' => 8,
            ],
            [
                'ProductName' => 'Kenda Nevegal 29x2.20 MTB Tires (Pair)',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Wheelsets',
                'Price' => 2400.00,
                'Stocks' => 30,
            ],

            // Drivetrain
            [
                'ProductName' => 'Shimano Deore M5100 1x11 Groupset',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Drivetrain',
                'Price' => 13500.00,
                'Stocks' => 10,
            ],
            [
                'ProductName' => 'SRAM SX Eagle 12-Speed Crankset',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Drivetrain',
                'Price' => 5200.00,
                'Stocks' => 18,
            ],

            // Braking System
            [
                'ProductName' => 'Shimano MT200 Hydraulic Disc Brakeset',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Braking System',
                'Price' => 2200.00,
                'Stocks' => 35,
            ],
            [
                'ProductName' => 'SRAM Centerline 160mm Disc Rotor',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Braking System',
                'Price' => 1100.00,
                'Stocks' => 40,
            ],

            // Miscellaneous
            [
                'ProductName' => 'Wellgo B087 Sealed Bearing Flat Pedals',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Miscellaneous',
                'Price' => 750.00,
                'Stocks' => 50,
            ],
            [
                'ProductName' => 'KMC X11 11-Speed Silver Chain',
                'Category' => 'Bike Parts',
                'SubCategory' => 'Miscellaneous',
                'Price' => 1250.00,
                'Stocks' => 28,
            ],


            // ==========================================
            // 3. ACCESSORIES
            // ==========================================
            // Safety Gear
            [
                'ProductName' => 'Giro Agilis MIPS Road Helmet',
                'Category' => 'Accessories',
                'SubCategory' => 'Safety Gear',
                'Price' => 4900.00,
                'Stocks' => 16,
            ],
            [
                'ProductName' => 'RockBros Breathable Full-Finger Gloves',
                'Category' => 'Accessories',
                'SubCategory' => 'Safety Gear',
                'Price' => 650.00,
                'Stocks' => 45,
            ],

            // Bike Attachments
            [
                'ProductName' => 'GUB G-85 Aluminum Phone Mount',
                'Category' => 'Accessories',
                'SubCategory' => 'Bike Attachments',
                'Price' => 450.00,
                'Stocks' => 60,
            ],
            [
                'ProductName' => 'Topeak Modula Java Adjustable Bottle Cage',
                'Category' => 'Accessories',
                'SubCategory' => 'Bike Attachments',
                'Price' => 890.00,
                'Stocks' => 32,
            ],

            // Security
            [
                'ProductName' => 'Kryptonite Keeper 785 Integrated Chain Lock',
                'Category' => 'Accessories',
                'SubCategory' => 'Security',
                'Price' => 2100.00,
                'Stocks' => 20,
            ],
            [
                'ProductName' => 'West Biking Heavy Duty U-Lock',
                'Category' => 'Accessories',
                'SubCategory' => 'Security',
                'Price' => 1150.00,
                'Stocks' => 25,
            ],

            // Storage
            [
                'ProductName' => 'RockBros Waterproof Saddle Bag',
                'Category' => 'Accessories',
                'SubCategory' => 'Storage',
                'Price' => 780.00,
                'Stocks' => 30,
            ],
            [
                'ProductName' => 'Topeak Explorer Tubular Rear Rack',
                'Category' => 'Accessories',
                'SubCategory' => 'Storage',
                'Price' => 2300.00,
                'Stocks' => 12,
            ],

            // Maintenance
            [
                'ProductName' => 'Park Tool SK-4 Home Mechanic Starter Kit',
                'Category' => 'Accessories',
                'SubCategory' => 'Maintenance',
                'Price' => 7800.00,
                'Stocks' => 6,
            ],
            [
                'ProductName' => 'Muc-Off Bio Degreaser & Chain Lube Kit',
                'Category' => 'Accessories',
                'SubCategory' => 'Maintenance',
                'Price' => 1450.00,
                'Stocks' => 22,
            ],

            // Miscellaneous
            [
                'ProductName' => 'RockBros Rechargeable 1000lm Headlight',
                'Category' => 'Accessories',
                'SubCategory' => 'Miscellaneous',
                'Price' => 1200.00,
                'Stocks' => 38,
            ],
            [
                'ProductName' => 'West Biking Mini High Pressure Hand Pump',
                'Category' => 'Accessories',
                'SubCategory' => 'Miscellaneous',
                'Price' => 580.00,
                'Stocks' => 40,
            ],
        ];

        // Using updateOrCreate prevents duplicate entries if you re-run the seeder
        foreach ($products as $item) {
            Product::updateOrCreate(
                [
                    'ProductName' => $item['ProductName'],
                    'Category'    => $item['Category'],
                    'SubCategory' => $item['SubCategory']
                ],
                [
                    'Price'  => $item['Price'],
                    'Stocks' => $item['Stocks']
                ]
            );
        }
    }
}