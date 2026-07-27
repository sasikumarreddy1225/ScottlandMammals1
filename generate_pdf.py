from reportlab.lib.pagesizes import letter
from reportlab.pdfgen import canvas
from reportlab.lib.units import inch
import textwrap

infile = "README.md"
outfile = "README.pdf"

with open(infile, 'r', encoding='utf-8') as f:
    text = f.read()

lines = []
for para in text.split('\n'):
    if para.strip() == '':
        lines.append('')
    else:
        wrapped = textwrap.wrap(para, width=100)
        if not wrapped:
            lines.append('')
        else:
            lines.extend(wrapped)

c = canvas.Canvas(outfile, pagesize=letter)
width, height = letter
y = height - inch
c.setFont("Helvetica", 10)

for line in lines:
    if y < inch:
        c.showPage()
        y = height - inch
        c.setFont("Helvetica", 10)
    c.drawString(inch, y, line)
    y -= 12

c.save()
print(f"WROTE {outfile}")
