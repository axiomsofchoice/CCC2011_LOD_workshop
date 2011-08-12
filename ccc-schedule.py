"""Convert the Chaos Communication Camp 2011 Schedule from XML to RDF.
"""

import urllib2
from lxml import etree
from StringIO import StringIO

def read_xml():
    source_schedule = 'http://events.ccc.de/camp/2011/Fahrplan/schedule.en.xml'
    
    req = urllib2.Request(source_schedule)
    r = urllib2.urlopen(req)
    s = r.read()
    sio = StringIO(s)
    
    tree = etree.parse(sio)
    
    return tree

rn = read_xml()

# Get overall conference metadata
myXpath = etree.ETXPath("/schedule/conference/title")
print myXpath(rn)[0].text

# TODO: extract the following metadata
"""
<title>Chaos Communication Camp 2011</title>
<subtitle>Project Flow Control</subtitle>
<venue>Luftfahrtmuseum Finowfurt</venue>
<city>Finowfurt</city>
<start>2011-08-10</start>
<end>2011-08-14</end>
<days>5</days>
<release>Version 1.3</release>
<day_change>06:00</day_change>
<timeslot_duration>00:30</timeslot_duration>
"""

# Iterate over the days of the conference
daysXpath = etree.ETXPath("/schedule/day")
days = daysXpath(rn)
for day in days:
    print day
    # Iterate over rooms for that day
    for room in day.iterfind("./room"):
        print room.get("name")
        #
        ## Iterate over events for this room on this day
        for event in room.iterfind("./event"):
            print event.get("id")
            
            # Iterate over persons
            # Iterate over links

exit(0)

from rdflib import Graph
from rdflib import Namespace
import StringIO

g = Graph()
g.parse(StringIO.StringIO(s), format="xml")

for s, p, o in g:
    if str(s)==exampleDOI and str(p)=="http://purl.org/dc/terms/title":
        print "%s has title \"%s\"" % (s,o)
