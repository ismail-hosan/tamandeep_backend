<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <title>About Me</title>
</head>

<body>
    <main class="font-inter min-h-screen flex px-5 md:px-8 items-center justify-center">
        <!-- card -->
        <div class="w-full max-w-xl">
            <!-- image -->
            <div class="h-52 md:h-72 w-full">
                <img class="h-full w-full object-cover rounded-md" src="https://i.postimg.cc/FRMZdVGP/sample-Image.png"
                    alt="" />
            </div>
            <!-- info -->
            <div>
                <h3 class="text-xl font-inter">My Profile</h3>

                <!-- save contact button -->
                <button
                    class="bg-primaryColor w-full font-inter text-sm cursor-pointer border-none outline-none text-white py-3 rounded-md">
                    Save Contact
                </button>

                <!-- form -->
                <div class="mt-5">
                    <form action="" class="font-inter space-y-4">
                        <label class="flex flex-col gap-2">
                            <label for="name"> First name </label>

                            <input
                                class="outline-none focus:outline-none border-black/10 px-4 py-3 rounded-md text-base"
                                type="text" name="name" value="TamanDeep" id="name" /></label>
                        <label class="flex flex-col gap-2">
                            <label for="name"> Description</label>

                            <textarea class="resize-none font-inter outline-none focus:outline-none border-black/10 px-4 py-3 rounded-md text-base"
                                rows="4" name="name" id="name">
Hello, This is me TamanDeep!</textarea>
                        </label>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
