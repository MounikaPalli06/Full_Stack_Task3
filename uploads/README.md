# Upload Directory

This directory stores user profile pictures and uploaded files.

## File Structure
- Profile pictures are stored with naming convention: `profile_{user_id}_{timestamp}.{extension}`
- Only JPG, JPEG, PNG, and GIF files are allowed
- Maximum file size: 2MB
- Files are automatically deleted when users update their profile picture

## Security
- Only authenticated users can upload files
- MIME type validation is enforced
- File names are sanitized to prevent directory traversal
- `index.php` is included to prevent directory listing
