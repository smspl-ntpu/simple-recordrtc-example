# Simple RecordRTC Example

This is a reorganized version of the RecordRTC audio recording example project.

## Project Structure

```
project/
├── public/              # Frontend files
│   ├── index.html      # Main HTML file
│   ├── assets/         # Static assets
│   │   ├── css/        # Stylesheets
│   │   │   └── style.css
│   │   └── js/         # JavaScript files
│   │       └── RecordRTC.js
│   └── uploads/        # Audio file uploads
├── backend/            # PHP backend
│   ├── save.php        # Save audio files
│   ├── delete.php      # Delete audio files
│   └── index.php       # PHP version of main page
└── docs/               # Documentation
    ├── README.md       # Original README
    └── LICENSE         # License file
```

## Setup

1. Make sure you have a web server with PHP support
2. Place the project files in your web server directory
3. Access `public/index.html` for the frontend
4. The backend PHP files are in the `backend/` directory

## Features

- Audio recording using RecordRTC
- Real-time audio visualization
- Save and delete recorded audio files
- Clean, organized file structure

## Note

The PHP files have been updated to work with the new directory structure. The uploads path now points to `../public/uploads/` relative to the backend files.