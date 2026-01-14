# Railway Deployment Setup

## Persistent Storage Configuration

StrongSuite stores member profile pictures in the filesystem. To prevent these files from being deleted during redeployments, you need to configure a persistent volume in Railway.

### Setup Instructions

#### 1. Add Volume in Railway Dashboard

1. Go to your Railway project dashboard
2. Click on your **StrongSuite service**
3. Navigate to the **Volumes** tab
4. Click **+ New Volume**
5. Configure the volume:
   - **Mount Path**: `/var/www/html/storage/app/public`
   - **Name**: `strongsuite-storage` (or any name you prefer)
6. Click **Add** to create the volume

#### 2. Deploy the Updated Code

After adding the volume, deploy these changes:

```bash
git add Dockerfile docker-entrypoint.sh
git commit -m "Add Railway persistent volume for member photos"
git push origin main
```

Railway will automatically redeploy with the persistent volume attached.

#### 3. Verify Setup

After deployment:

1. Upload a member profile picture through the application
2. Trigger a new deployment (push a small change or redeploy manually)
3. Check if the member's photo is still visible after redeployment

If the photo persists, the volume is configured correctly! ✅

---

## How It Works

- **Volume Mount**: Railway mounts `/var/www/html/storage/app/public` as a persistent volume
- **Dashboard Configuration**: Volume is configured entirely in Railway dashboard (NOT in Dockerfile)
- **Entrypoint**: Sets correct permissions on the volume at startup via `docker-entrypoint.sh`
- **Persistence**: All files in this directory survive across deployments

⚠️ **Important**: Railway does NOT allow the `VOLUME` keyword in Dockerfiles. Volumes must be configured through the Railway dashboard only.

---

## Important Notes

⚠️ **Volume Limitations:**
- Volumes are tied to a specific Railway project/service
- If you delete the volume, all stored files are permanently lost
- Volumes cannot be shared across multiple Railway projects

💡 **Future Migration:**
For production at scale, consider migrating to cloud storage (S3, DigitalOcean Spaces, Cloudflare R2) for:
- CDN support
- Better scalability
- Cross-platform portability
- Backup capabilities

---

## Troubleshooting

### Photos Still Disappearing

1. **Verify volume is attached**: Railway dashboard → Service → Volumes tab
2. **Check mount path**: Must be exactly `/var/www/html/storage/app/public`
3. **Review deployment logs**: Look for "Setting storage permissions..." message
4. **Check symlink**: Ensure `public/storage` → `storage/app/public` link exists

### Permission Errors

If you see permission denied errors:

1. Check docker-entrypoint.sh runs `chmod -R 777` on storage directories
2. Verify the volume has write permissions in Railway
3. Redeploy to trigger permission updates

### Need Help?

Check Railway logs for detailed error messages:
```bash
railway logs
```
