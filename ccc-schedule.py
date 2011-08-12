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
myXapth = etree.ETXPath("/schedule/conference/title")
print myXpath(rn)

# Iterate over the days of the conference

#    # Iterate over rooms for that day
#        
#        # Iterate over events for this room on this day

exit(0)

from rdflib import Graph
from rdflib import Namespace
import StringIO

g = Graph()
g.parse(StringIO.StringIO(s), format="xml")

for s, p, o in g:
    if str(s)==exampleDOI and str(p)=="http://purl.org/dc/terms/title":
        print "%s has title \"%s\"" % (s,o)
