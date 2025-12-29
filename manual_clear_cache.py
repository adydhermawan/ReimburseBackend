import ftplib
import sys

# Credentials from .env.production
FTP_HOST = "ftpupload.net"
FTP_USER = "if0_40730583"
FTP_PASS = "jarangN0NT0N"
FTP_PORT = 21

CACHE_DIR = "recashly.dadi.web.id/htdocs/bootstrap/cache"

def clear_cache():
    ftp = ftplib.FTP()
    try:
        print(f"Connecting to {FTP_HOST}...")
        ftp.connect(FTP_HOST, FTP_PORT)
        print(f"Logging in as {FTP_USER}...")
        ftp.login(FTP_USER, FTP_PASS)
        
        print(f"Changing directory to {CACHE_DIR}...")
        ftp.cwd(CACHE_DIR)
        
        # List files
        files = ftp.nlst()
        print(f"Files in cache: {files}")
        
        for file in files:
            if file == "." or file == ".." or file == ".gitignore":
                continue
            if file.endswith(".php"):
                try:
                    print(f"Deleting {file}...")
                    ftp.delete(file)
                    print("Deleted.")
                except Exception as e:
                    print(f"Failed to delete {file}: {e}")
            
        print("Manual cache clear complete!")
        
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)
    finally:
        try:
            ftp.quit()
        except:
            pass

if __name__ == "__main__":
    clear_cache()
