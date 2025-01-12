<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <title>About Me</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap') @tailwind base;

        .mt-5 {
            margin-top: 1.25rem;
        }

        .flex {
            display: flex;
        }

        .size-16 {
            width: 4rem;
            height: 4rem;
        }

        .h-20 {
            height: 5rem;
        }

        .h-52 {
            height: 13rem;
        }

        .h-full {
            height: 100%;
        }

        .min-h-screen {
            min-height: 100vh;
        }

        .w-full {
            width: 100%;
        }

        .max-w-xl {
            max-width: 36rem;
        }

        .transform {
            transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .resize-none {
            resize: none;
        }

        .flex-col {
            flex-direction: column;
        }

        .items-center {
            align-items: center;
        }

        .justify-center {
            justify-content: center;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .space-y-4> :not([hidden])~ :not([hidden]) {
            --tw-space-y-reverse: 0;
            margin-top: calc(1rem * calc(1 - var(--tw-space-y-reverse)));
            margin-bottom: calc(1rem * var(--tw-space-y-reverse));
        }

        .rounded-md {
            border-radius: 0.375rem;
        }

        .border-none {
            border-style: none;
        }

        .border-black\/10 {
            border-color: rgb(0 0 0 / 0.1);
        }

        .bg-primaryColor {
            --tw-bg-opacity: 1;
            background-color: rgb(40 141 255 / var(--tw-bg-opacity, 1));
        }

        .object-cover {
            -o-object-fit: cover;
            object-fit: cover;
        }

        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .px-5 {
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .font-inter {
            font-family: Inter, serif;
        }

        .text-base {
            font-size: 1rem;
            line-height: 1.5rem;
        }

        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        .text-xl {
            font-size: 1.25rem;
            line-height: 1.75rem;
        }

        .text-white {
            --tw-text-opacity: 1;
            color: rgb(255 255 255 / var(--tw-text-opacity, 1));
        }

        .outline-none {
            outline: 2px solid transparent;
            outline-offset: 2px;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .focus\:outline-none:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
        }

        @media (min-width: 768px) {
            .md\:h-72 {
                height: 18rem;
            }

            .md\:px-8 {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
    </style>
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
