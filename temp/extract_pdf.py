import fitz # PyMuPDF
import os

pdf_path = r'c:\xampp\htdocs\WP-class\outputs\WP Project Report Format.pdf'
out_dir = r'c:\xampp\htdocs\WP-class\outputs\pdf_extracts'

os.makedirs(out_dir, exist_ok=True)

try:
    doc = fitz.open(pdf_path)
    text = ""
    for i in range(len(doc)):
        page = doc[i]
        text += page.get_text()
        
        # Extract images
        image_list = page.get_images(full=True)
        for img_index, img in enumerate(image_list):
            xref = img[0]
            base_image = doc.extract_image(xref)
            image_bytes = base_image["image"]
            image_ext = base_image["ext"]
            image_path = os.path.join(out_dir, f"image_page{i+1}_{img_index}.{image_ext}")
            with open(image_path, "wb") as f:
                f.write(image_bytes)

    with open(os.path.join(out_dir, 'extracted_text.txt'), 'w', encoding='utf-8') as f:
        f.write(text)
    
    # Also grab screenshot paths perfectly
    screenshots = {}
    base_out = r'c:\xampp\htdocs\WP-class\outputs'
    for f in os.listdir(base_out):
        folder_path = os.path.join(base_out, f)
        if os.path.isdir(folder_path) and f != 'pdf_extracts' and f != 'DATABASE':
            files = os.listdir(folder_path)
            pngs = sorted([p for p in files if p.endswith('.png') or p.endswith('.jpg')])
            if pngs:
                # Store relative path for README
                screenshots[f] = f"outputs/{f}/{pngs[0]}"
                
    import json
    with open(os.path.join(out_dir, 'screenshots.json'), 'w') as f:
        json.dump(screenshots, f)
        
    print("Extraction done.")
except Exception as e:
    import traceback
    traceback.print_exc()
