# Deploying P4PDF to Vercel

This guide will help you deploy and test the P4PDF library on Vercel.

## 🚀 Quick Start

### Option 1: Test Locally First (Recommended)

1. **Install Dependencies**
   ```bash
   cd P4PDF
   composer install
   ```

2. **Start Local PHP Server**
   ```bash
   # From the P4PDF directory
   php -S localhost:8000 -t .
   ```

3. **Open in Browser**
   - Open `http://localhost:8000/public/index.html` in your browser
   - Enter your iLovePDF API keys
   - Upload a PDF and test compression

4. **Test API Directly**
   - You can also test the API endpoint directly:
   ```bash
   curl -X POST "http://localhost:8000/api/compress.php?public_key=YOUR_KEY&secret_key=YOUR_SECRET&compression_level=recommended" \
        -F "file=@/path/to/your/file.pdf"
   ```

### Option 2: Deploy to Vercel

1. **Install Vercel CLI**
   ```bash
   npm install -g vercel
   ```

2. **Install PHP Dependencies**
   ```bash
   cd P4PDF
   composer install --no-dev --optimize-autoloader
   ```

3. **Set Environment Variables (Optional but Recommended)**
   
   You can set your API keys as environment variables in Vercel:
   - Go to your Vercel project settings
   - Add environment variables:
     - `P4PDF_PUBLIC_KEY` = your public key
     - `P4PDF_SECRET_KEY` = your secret key
   
   Or pass them as query parameters (less secure but works for testing).

4. **Deploy**
   ```bash
   vercel
   ```
   
   Follow the prompts to link your project.

5. **Access Your Deployed App**
   - Vercel will provide you with a URL
   - Visit: `https://your-project.vercel.app`
   - The HTML interface will be at the root URL
   - API endpoints will be at `/api/*`

## 📁 Project Structure

```
P4PDF/
├── api/
│   └── compress.php          # API endpoint for compression
├── public/
│   └── index.html            # Frontend interface
├── src/                      # P4PDF library source
├── vendor/                   # Composer dependencies
├── vercel.json              # Vercel configuration
└── composer.json            # PHP dependencies
```

## 🔧 Configuration

### Environment Variables

In Vercel dashboard, you can set:
- `P4PDF_PUBLIC_KEY` - Your iLovePDF public API key
- `P4PDF_SECRET_KEY` - Your iLovePDF secret API key

### API Usage

**Endpoint:** `POST /api/compress`

**Query Parameters:**
- `public_key` (required) - Your public API key
- `secret_key` (required) - Your secret API key  
- `compression_level` (optional) - `low`, `recommended`, or `extreme` (default: `recommended`)

**Body:**
- `file` (multipart/form-data) - The PDF file to compress

**Response:**
```json
{
  "success": true,
  "filename": "compressed.pdf",
  "data": "base64_encoded_pdf_data",
  "size": 12345
}
```

## 🧪 Testing Locally

### Using PHP Built-in Server

```bash
cd P4PDF
php -S localhost:8000 -t .
```

Then open: `http://localhost:8000/public/index.html`

### Using cURL

```bash
curl -X POST "http://localhost:8000/api/compress.php?public_key=YOUR_PUBLIC_KEY&secret_key=YOUR_SECRET_KEY" \
     -F "file=@test.pdf" \
     -o compressed.pdf
```

## 📝 Notes

1. **File Size Limits**: Vercel has a 4.5MB limit for request body size. For larger files, consider:
   - Using direct upload to iLovePDF API
   - Implementing chunked upload
   - Using a different hosting solution for large files

2. **Timeout Limits**: Vercel serverless functions have timeout limits:
   - Hobby: 10 seconds
   - Pro: 60 seconds
   
   PDF compression can take time, so you may need to handle this with:
   - Webhooks for async processing
   - Polling for task completion

3. **Composer Dependencies**: Make sure to include `vendor/` in your repository or configure Vercel to run `composer install` during build.

4. **API Keys**: For production, always use environment variables, never hardcode keys in your code.

## 🔐 Security Best Practices

1. Use environment variables for API keys
2. Add CORS restrictions in production
3. Implement rate limiting
4. Validate file types and sizes
5. Sanitize all inputs

## 🐛 Troubleshooting

**Issue: Composer dependencies not found**
- Make sure `composer install` runs during build
- Check that `vendor/` directory is included in deployment

**Issue: API endpoint not found**
- Verify `vercel.json` routes are correct
- Check that `api/compress.php` exists

**Issue: File upload errors**
- Check file size limits
- Verify file is a valid PDF
- Check API key permissions

**Issue: Timeout errors**
- Large PDFs may exceed timeout limits
- Consider using webhooks for async processing

## 📚 Additional Resources

- [Vercel PHP Runtime](https://github.com/vercel-community/php)
- [iLovePDF API Documentation](https://developer.ilovepdf.com/docs)
- [Get API Keys](https://developer.ilovepdf.com/user/projects)

