import ftplib
import sys

# Credentials from .env.production
FTP_HOST = "ftpupload.net"
FTP_USER = "if0_40730583"
FTP_PASS = "jarangN0NT0N"
FTP_PORT = 21

REMOTE_DIR = "recashly.dadi.web.id/htdocs" 
# test_api_login.php is likely in root or public? User said "test_api_login.php". 
# Usually if accessed via URL it's in public OR root if root is public.
# Based on previous context, root is htdocs, public is htdocs/public.
# If user accesses recashly.dadi.web.id/test_api_login.php, it should be in public.
# But checking local path: /Users/adydhermawan/Projects/ReimburseBackend/test_api_login.php
# It seems it is in project root locally.
# I should put it in `public/` to be accessible via URL.

def upload_test():
    ftp = ftplib.FTP()
    try:
        print(f"Connecting to {FTP_HOST}...")
        ftp.connect(FTP_HOST, FTP_PORT)
        ftp.login(FTP_USER, FTP_PASS)
        
        local_file = "test_api_login.php"
        remote_file = "recashly.dadi.web.id/htdocs/public/test_api_login.php"
        
        print(f"Uploading {local_file} to {remote_file}...")
        with open(local_file, "rb") as f:
            ftp.storbinary(f"STOR {remote_file}", f)
            
        print("Upload successful!")
        
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)
    finally:
        try:
            ftp.quit()
        except:
            pass

if __name__ == "__main__":
    upload_test()
