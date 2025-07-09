<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file
$logFile = __DIR__ . '/email_debug.log';
file_put_contents($logFile, "=== New Email Attempt ===\n", FILE_APPEND);

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Check if vendor directory exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    writeLog("Error: Vendor autoload.php not found");
    http_response_code(500);
    echo json_encode([
        "error" => "PHPMailer is not installed. Please run 'composer install' in the PHP directory.",
        "debug_info" => [
            "missing_file" => __DIR__ . '/vendor/autoload.php',
            "current_dir" => __DIR__
        ]
    ]);
    exit();
}

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    writeLog("Starting email process");
    
    // Check if PHPMailer classes exist
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        throw new Exception("PHPMailer class not found. Please check your installation.");
    }

    $email = $_POST['userEmail'] ?? '';
    $imgid = $_POST['imageId'] ?? '';
    $name = $_POST['name'] ?? '';  

    writeLog("Received data - Email: $email, ImageID: $imgid, Name: $name");

    // Validate inputs
    if (empty($email) || empty($imgid) || empty($name)) {
        throw new Exception("Email, Image ID, or Name is missing!");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format!");
    }

    $config = require_once 'email_config.php';
    writeLog("Loaded configuration");
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
        $mail->Debugoutput = function($str, $level) {
            writeLog("SMTP Debug: $str");
        };

        writeLog("Setting up SMTP connection");
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['smtp_port'];
        
        // Additional SMTP settings for better deliverability
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Set priority
        $mail->Priority = 1;
        
        // Set additional headers to improve deliverability
        $mail->XMailer = 'Yumify Mailer';
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->addCustomHeader('List-Unsubscribe', '<mailto:' . $config['from_email'] . '?subject=unsubscribe>');
        
        writeLog("SMTP Configuration set");
        writeLog("Host: " . $config['smtp_host']);
        writeLog("Port: " . $config['smtp_port']);
        writeLog("Username: " . $config['smtp_username']);
        writeLog("Secure: " . PHPMailer::ENCRYPTION_STARTTLS);

        // Set timeout
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = true;

        writeLog("Setting up email content");
        // Recipients
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email, $name);
        $mail->addReplyTo($config['from_email'], $config['from_name']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your Requested Recipe from Yumify";

        // Prepare email content with improved HTML structure
        $to = $email;
        $sub = "Recipe Details from Our Platform";
        $ingredients = '';
        $process = '';
        $firstline = '';
        if($imgid == 'one'){
            $firstline = "Here's a delicious cheesecake recipe for you!";
            $ingredients = "
            For the crust:
            1) 1 ½ cups graham cracker crumbs
            2) ¼ cup sugar
            3) ½ cup unsalted butter, melted

            For the filling:
            1) 4 (8 oz) packages cream cheese, softened
            2) 1 ¼ cups sugar
            3) 1 teaspoon vanilla extract
            4) 4 large eggs
            5) 1 cup sour cream
            6) ¼ cup all-purpose flour (optional, for a firmer texture)

            For the topping (optional):
            1) Fresh fruit, chocolate sauce, or caramel sauce
            ";

            $process = "
            Instructions:
            Preheat the Oven to 325°F (160°C).

            Prepare the Crust:
            Combine graham cracker crumbs, sugar, and melted butter.
            Press mixture into a 9-inch springform pan and bake for 10 minutes.

            Make the Filling:
            Beat softened cream cheese with sugar and vanilla.
            Add eggs one at a time. Add sour cream and flour (optional).

            Pour the Filling:
            Pour over the cooled crust and bake for 55-70 minutes.
            Turn off the oven and let it cool for an hour, then refrigerate.

            Serve:
            Top with fresh fruit or sauce and serve.
            ";
        }
        else if($imgid == 'two'){
            $firstline = "Here's a flavorful chicken biryani recipe for you!";
            $ingredients = "
            For the chicken marinade:

                1)1 ½ lbs (700g) chicken, cut into pieces
                2)1 cup plain yogurt
                3)2 tablespoons ginger-garlic paste
                4)2 teaspoons red chili powder
                5)1 teaspoon turmeric powder
                6)2 teaspoons garam masala
                7)Salt to taste
                8)Juice of 1 lemon
                9)2 tablespoons chopped fresh cilantro
                10)2 tablespoons chopped fresh mint
            For the rice:

                1)2 cups basmati rice
                2)4 cups water
                3)2-3 whole cloves
                4)2-3 green cardamom pods
                5)1-2 bay leaves
                6)1 cinnamon stick
                7)Salt to taste
            For the biryani:

                1)2 tablespoons ghee or oil
                2)2 large onions, thinly sliced
                3)1-2 green chilies, slit (optional)
                4)½ cup fried onions (store-bought or homemade)
                5)Additional chopped cilantro and mint for garnish
            ";
            $process = "
            Marinate the Chicken:

                In a large bowl, combine yogurt, ginger-garlic paste, red chili powder, turmeric powder, garam masala, salt, lemon juice, cilantro, and mint.
                Add the chicken pieces, mix well, and let it marinate for at least 1 hour (or overnight in the refrigerator for best results).
            Prepare the Rice:

                Rinse the basmati rice under cold water until the water runs clear. Soak it in water for 30 minutes, then drain.
                In a large pot, bring 4 cups of water to a boil. Add whole cloves, cardamom pods, bay leaves, cinnamon stick, and salt.
                Add the soaked and drained rice to the boiling water. Cook until the rice is 70% cooked (about 5-7 minutes), then drain and set aside.
            Cook the Chicken:

                In a large, heavy-bottomed pot or Dutch oven, heat ghee or oil over medium heat.
                Add the sliced onions and sauté until golden brown.
                Add the marinated chicken and green chilies (if using). Cook until the chicken is browned and cooked through (about 10-15 minutes).
            Layer the Biryani:

                Once the chicken is cooked, reduce the heat to low. Layer the partially cooked rice over the chicken.
                Sprinkle fried onions, additional chopped cilantro, and mint on top.
            Dum Cooking:

                Cover the pot with a tight-fitting lid. You can seal it with dough or a clean kitchen towel to trap the steam.
                Cook on low heat for about 25-30 minutes to allow the flavors to meld and the rice to finish cooking.
            Serve:

                Gently fluff the biryani with a fork before serving.
                Serve hot with raita (yogurt sauce) or salad.
                Enjoy your delicious chicken biryani!    ";
        }
        else if($imgid == 'three'){
            $firstline="Here's a rich and fudgy chocolate brownie recipe for you!";
            $ingredients = "
                1)1 cup (225g) unsalted butter
                2)2 cups (400g) granulated sugar
                3)4 large eggs
                4)1 teaspoon vanilla extract
                5)1 cup (125g) all-purpose flour
                6)1 cup (90g) unsweetened cocoa powder
                7)½ teaspoon salt
                8)½ teaspoon baking powder
                9)1 cup (175g) chocolate chips or chopped nuts (optional)
            ";
            $process = "
            Preheat the Oven:
                Preheat your oven to 350°F (175°C). Grease and line a 9x13 inch (23x33 cm) baking pan with parchment paper.

            Melt the Butter:
                In a medium saucepan, melt the butter over low heat. Remove from heat and let it cool slightly.

            Mix in Sugar and Eggs:

                Stir in the granulated sugar until well combined.
                Add the eggs one at a time, mixing well after each addition.
                Stir in the vanilla extract.
            Combine Dry Ingredients:
                In a separate bowl, whisk together the flour, cocoa powder, salt, and baking powder.

            Combine Wet and Dry Ingredients:
                Gradually add the dry ingredients to the wet mixture, stirring until just combined. Be careful not to overmix.

            Add Chocolate Chips (Optional):
                If using, fold in the chocolate chips or nuts.

            Pour into the Pan:
                Pour the brownie batter into the prepared baking pan, spreading it evenly.

            Bake:
                Bake in the preheated oven for 20-25 minutes, or until a toothpick inserted into the center comes out with a few moist crumbs (not wet batter).

            Cool and Serve:
                Allow the brownies to cool in the pan for about 10 minutes before transferring them to a wire rack to cool completely.
                Once cooled, cut into squares and enjoy!

            Enjoy your delicious chocolate brownies!
            ";
        }
        else if($imgid == 'four'){
            $firstline="Here's a tasty chicken pizza recipe for you!";
            $ingredients = "
            For the pizza dough:

                1)2 ¼ teaspoons (1 packet) active dry yeast
                2)1 ½ cups warm water (about 110°F or 43°C)
                3)3 ½ to 4 cups all-purpose flour
                4)2 tablespoons olive oil
                5)1 teaspoon sugar
                6)1 teaspoon salt
            For the chicken topping:

                1)1 cup cooked chicken, shredded or diced
                2)1 tablespoon olive oil
                3)1 teaspoon garlic powder
                4)1 teaspoon paprika
                5)Salt and pepper to taste
            For the pizza assembly:

                1)1 cup pizza sauce (store-bought or homemade)
                2)2 cups shredded mozzarella cheese
                3)½ cup sliced bell peppers (optional)
                4)½ cup sliced red onion (optional)
                5)½ teaspoon dried oregano or Italian seasoning (optional)
                6)Fresh basil leaves for garnish (optional)
            ";
            $process = "
            Make the Pizza Dough:

                In a small bowl, dissolve the yeast and sugar in warm water. Let it sit for about 5-10 minutes until frothy.
                In a large mixing bowl, combine 3 ½ cups of flour and salt. Make a well in the center and add the yeast mixture and olive oil.
                Mix until a dough forms. If the dough is too sticky, gradually add more flour until it's manageable.
                Knead the dough on a floured surface for about 5-7 minutes until smooth and elastic.
                Place the dough in a greased bowl, cover with a clean cloth, and let it rise in a warm place for about 1 hour, or until doubled in size.
            Prepare the Chicken Topping:

                In a bowl, combine the cooked chicken, olive oil, garlic powder, paprika, salt, and pepper. Mix well and set aside.
            Preheat the Oven:

                Preheat your oven to 475°F (245°C). If using a pizza stone, place it in the oven to heat.
            Shape the Pizza:

                Once the dough has risen, punch it down and divide it into two portions for two pizzas or keep it whole for one large pizza.
                Roll out the dough on a floured surface to your desired thickness (about ¼ inch thick).
                Transfer the rolled-out dough to a pizza peel or a baking sheet lined with parchment paper.
            Assemble the Pizza:

                Spread pizza sauce evenly over the dough.
                Sprinkle shredded mozzarella cheese on top.
                Distribute the seasoned chicken evenly over the cheese.
                Add sliced bell peppers and red onion if desired.
                Sprinkle with dried oregano or Italian seasoning for extra flavor.
            Bake the Pizza:

                Carefully transfer the pizza to the preheated oven (or onto the pizza stone).
                Bake for about 12-15 minutes, or until the crust is golden and the cheese is bubbly and melted.
            Serve:

                Remove the pizza from the oven and let it cool for a few minutes.
                Garnish with fresh basil leaves if desired, slice, and enjoy!
                Enjoy your delicious chicken pizza!
            ";
        }
        else if($imgid == 'five'){
            $firstline = "Here's a simple and delicious recipe for sautéed eggs with avocado on the side!";
            $ingredients   = "
                1)4 large eggs
                2)1 tablespoon olive oil or butter
                3)Salt and pepper to taste
                4)1 ripe avocado
                5)1 tablespoon lime or lemon juice
                6)Optional toppings: red pepper flakes, fresh herbs (like cilantro or parsley), or sliced tomatoes
            ";
            $process = "
            Prepare the Avocado:

                Cut the avocado in half, remove the pit, and scoop the flesh into a bowl.
                Mash it slightly with a fork, adding lime or lemon juice, salt, and pepper to taste. Set aside.
            Sauté the Eggs:

                In a non-stick skillet, heat the olive oil or butter over medium heat.
                Crack the eggs into the skillet. You can cook them sunny-side up, over-easy, or scrambled, depending on your preference.
                Season with salt and pepper. Cook until the whites are set and the yolks are cooked to your liking (about 2-4 minutes for sunny-side up).
            Serve:

                Plate the sautéed eggs alongside the mashed avocado.
                Add optional toppings such as red pepper flakes, fresh herbs, or sliced tomatoes if desired.
                Enjoy your sautéed eggs with avocado!
            ";
        }
        else if($imgid == 'six'){
            $firstline="Here's a classic pancake recipe for you!";
            $ingredients = "
                1)1 cup all-purpose flour
                2)2 tablespoons sugar
                3)1 tablespoon baking powder
                4)½ teaspoon salt
                5)1 cup milk
                6)1 large egg
                7)2 tablespoons melted butter (plus extra for cooking)
                8)1 teaspoon vanilla extract (optional)
            ";
            $process = "
            Mix Dry Ingredients:

                In a large bowl, whisk together the flour, sugar, baking powder, and salt.
            Combine Wet Ingredients:

                In another bowl, mix the milk, egg, melted butter, and vanilla extract until well combined.
            Combine Both Mixtures:

                Pour the wet ingredients into the dry ingredients. Stir gently until just combined. It's okay if there are a few lumps; do not overmix.
            Preheat the Pan:

                Heat a non-stick skillet or griddle over medium heat. Add a little butter to coat the surface.
            Cook the Pancakes:

                Pour about ¼ cup of batter onto the skillet for each pancake. Cook until bubbles form on the surface (about 2-3 minutes), then flip and cook for another 1-2 minutes until golden brown.
            Serve:

                Keep the pancakes warm in a low oven while you cook the remaining batter.
                Serve with your favorite toppings like maple syrup, fresh fruit, whipped cream, or nuts.
                Enjoy your delicious pancakes!
            ";
        }
        else if($imgid == 'seven'){
            $firstline='Heres a simple and delicious ramen recipe for you!';
            $ingredients = "
            For the broth:

                1)4 cups chicken or vegetable broth
                2)2 cups water
                3)2 tablespoons soy sauce
                4)1 tablespoon miso paste (optional)
                5)1 tablespoon sesame oil
                6)1-inch piece of ginger, sliced
                7)2 cloves garlic, minced
            For the ramen:

                1)2 servings of ramen noodles (fresh or dried)
                2)1 cup cooked chicken, sliced (or tofu for a vegetarian option)
                3)2 soft-boiled eggs (optional)   
                4)1 cup spinach or bok choy
                5)½ cup sliced mushrooms (shiitake or button)
                6)2 green onions, sliced
                7)Nori (seaweed) sheets, for garnish
                8)Sesame seeds, for garnish
            ";
            $process = "
            Prepare the Broth:

                In a large pot, combine the chicken or vegetable broth, water, soy sauce, miso paste, sesame oil, ginger, and garlic.
                Bring to a simmer and let it cook for about 15-20 minutes to allow the flavors to meld. Remove the ginger slices before serving.
            Cook the Ramen Noodles:

                In a separate pot, cook the ramen noodles according to the package instructions. Drain and set aside.
            Assemble the Ramen:

                Divide the cooked noodles into bowls.
                Pour the hot broth over the noodles.
                Top with sliced chicken or tofu, soft-boiled eggs, spinach or bok choy, mushrooms, and green onions.
            Garnish and Serve:

                Add nori sheets and sprinkle with sesame seeds.
                Serve hot and enjoy!
                Feel free to customize your ramen with additional toppings like corn, bamboo shoots, or chili oil! Enjoy your homemade ramen!
            ";
        }

        // Prepare email content
        $message = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recipe Details from Yumify</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <header style='background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px;'>
                <h1 style='color: #ff4b28; margin: 0;'>Yumify</h1>
            </header>
            
            <main style='padding: 20px 0;'>
                <h2 style='color: #333;'>Hello $name,</h2>
                <p style='margin-bottom: 20px;'>Thank you for using Yumify. Here's your requested recipe:</p>
                
                <div style='background-color: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 5px;'>
                    <h3 style='color: #ff4b28; margin-top: 0;'>$firstline</h3>
                    
                    <div style='margin: 20px 0;'>
                        <h4 style='color: #666;'>Ingredients:</h4>
                        <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>
                            $ingredients
                        </div>
                    </div>
                    
                    <div style='margin: 20px 0;'>
                        <h4 style='color: #666;'>Instructions:</h4>
                        <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>
                            $process
                        </div>
                    </div>
                </div>
            </main>
            
            <footer style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>
                <p style='color: #666; font-size: 14px;'>Happy Cooking! 🍳</p>
                <p style='color: #999; font-size: 12px;'>
                    This email was sent from Yumify Recipe Sharing Platform. 
                    <br>
                    To unsubscribe, please <a href='mailto:$config[from_email]?subject=unsubscribe' style='color: #ff4b28;'>click here</a>
                </p>
            </footer>
        </body>
        </html>";

        $mail->Body = $message;
        // Create plain text version
        $mail->AltBody = strip_tags(str_replace(
            ['<br>', '</p>', '</h1>', '</h2>', '</h3>', '</h4>'], 
            ["\n", "\n\n", "\n\n", "\n\n", "\n\n", "\n\n"], 
            $message
        ));

        writeLog("Attempting SMTP connection and sending email");
        // Send email
        if(!$mail->send()) {
            writeLog("Send Error: " . $mail->ErrorInfo);
            throw new Exception("Mailer Error: " . $mail->ErrorInfo);
        }

        writeLog("Email sent successfully!");
        http_response_code(200);
        echo json_encode([
            "success" => true, 
            "message" => "Email sent successfully!",
            "debug_log" => "Check email_debug.log for details"
        ]);

    } catch (Exception $e) {
        writeLog("PHPMailer Exception: " . $e->getMessage());
        throw new Exception("Failed to send email: " . $e->getMessage());
    }

} catch (Exception $e) {
    writeLog("General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "error" => "Email could not be sent. Error: " . $e->getMessage(),
        "debug_info" => [
            "error_message" => $e->getMessage(),
            "error_trace" => $e->getTraceAsString(),
            "debug_log" => "Check email_debug.log for details"
        ]
    ]);
}
?>
