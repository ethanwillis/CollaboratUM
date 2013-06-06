from numpy import zeros
from numpy import mat
#from scipy.linalg import svd
#following needed for TFIDF
from math import log
from numpy import asarray, sum
from numpy import zeros
#from scipy.linalg import svd
from string import maketrans 
from string import replace
from string import count
from operator import itemgetter, attrgetter
import getopt
import socket
import threading
import sys
from decimal import *
import psycopg2
import MySQLdb
from operator import itemgetter, attrgetter
import os

stopwords = ""
replacetable=''  
ignorechars =''
facultyarr=[]
class Faculty:
        def __init__(self, name,  matchingdegree):
            self.name = str(name)
           
            self.matchingdegree = matchingdegree
        def __repr__(self):
            return repr((self.name, self.matchingdegree))
        def printfaculty(self):\
            return self.name.ljust(15)+str(format(self.matchingdegree, 'f'))
class LSA(object):
    def __init__(self, stopwords, ignorechars,queries,cursor,cursor2):
        self.stopwords = stopwords
        self.ignorechars = ignorechars
        self.replacetable = replacetable
        self.wdict = {}
        self.queries=queries
        self.cursor=cursor
        self.dcount = 0 
        self.cursor2=cursor2
    def parse(self):
        self.cursor.execute("SELECT * FROM dictionary;")
        linenum=1
        self.wordnum=int (self.cursor.rowcount)
        for i in self.cursor:
          
          
          
          
          
        
          
          #import pdb; pdb.set_trace()      
          for w in i:
            w1 = w.lower()      
            
            if not (w1 in self.wdict):
                
             self.wdict[w1] = [linenum]
          linenum=linenum+1 
    
          
        
              
    def build(self):
        #import pdb; pdb.set_trace()

        linenum=0
        self.A = zeros([len(self.wdict.keys()), self.facultynum])
        self.cursor.execute("SELECT * FROM Doc_term_frequency_matrix;")
        for i in self.cursor:
          colnum=0
          for j in i:
            w1 = float(j)      
            
            self.A[linenum,colnum] = w1 
            #import pdb; pdb.set_trace()        
            colnum=colnum+1
          linenum=linenum+1
        
    def calc(self):
        self.U, self.S, self.Vt = svd(self.A)
    def TFIDF(self):
        WordsPerDoc = sum(self.A, axis=0)        
        DocsPerWord = sum(asarray(self.A > 0, 'i'), axis=1)
        rows, cols = self.A.shape
        for i in range(rows):
            for j in range(cols):
                self.A[i,j] = (self.A[i,j] / WordsPerDoc[j]) * log(float(cols) / DocsPerWord[i])
   
    def queryA(self,s,t1):
        returnstr=""
        self.linum=1
        valid=False;
        #self.linum=len(self.queries)
        #import pdb; pdb.set_trace() 
        
         
          
         
        self.resultarr=zeros([self.linum,self.facultynum])
        self.validkeys=zeros([1,self.linum])
        
      
         
        for currlinum in range(0,self.linum):  
         # if not self.queries[currlinum]: break
          if not s: break
          words = s.split();
          #import pdb; pdb.set_trace() 
          
          for w in words:
            w1 = w.lower()      
            
            if  (w1 in self.wdict):
               self.validkeys[0,currlinum]=self.validkeys[0,currlinum]+1
               self.cursor.execute("SELECT col2, col1  FROM doc_term_frequency_matrix WHERE col0=%s ;",(self.wdict[w1][0],))
               for data1 in self.cursor:
                 
                 #if self.cursor.rowcount>0:
                  #data1=self.cursor.fetchone()
                 # print "rownum"+" "+str(self.wdict[w1][0])+ "rownum"+" "+str(k) 
                  #print "resutl num"+str(self.cursor.rowcount)
                  self.resultarr[currlinum,data1[1]-1]=self.resultarr[currlinum,data1[1]-1]+float(data1[0]) 
                 # change the global index into the one in its own category when filter is done at first 
                 
                 
          #import pdb; pdb.set_trace()       
          if self.validkeys[0,currlinum]>0:
           self.resultarr[currlinum,0:self.facultynum]=self.resultarr[currlinum,0:self.facultynum]/self.validkeys[0,currlinum]
        #f4=open("/var/www/html/Collaboratum/query/queryresults.txt","w+")
        self.keptnum=0 
        self.keptarr=[]
        if  ',' in t1:
          typekeptarr=t1.split(',')
          #import pdb; pdb.set_trace()
          for j in typekeptarr:
             typekept=int(j)
             if typekept==2: 
               self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("investigator",))
             if typekept==1: 
               self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("grant",))
             if typekept==3: 
               self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("class",))
             if typekept==0: 
                self.cursor2.execute("SELECT investigator_id FROM investigator ;")
             self.keptnum=self.keptnum+int (self.cursor2.rowcount)
             for i in self.cursor2:
               self.keptarr.append(int(i[0]))  
            #import pdb; pdb.set_trace() 
        else:
          typekept=int(t1)
          
          if typekept==2: 
            self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("investigator",))
          if typekept==1: 
            self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("grant",))
          if typekept==3: 
            self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("class",))
          if typekept==0: 
            self.cursor2.execute("SELECT investigator_id FROM investigator ;")
          self.keptnum=int (self.cursor2.rowcount)
          for i in self.cursor2:
            self.keptarr.append(int(i[0])) 
        for i in range(self.linum):
         if self.validkeys[0,i]>0:
                 
          currlist=[]
          currnum=0
          for j in range(self.facultynum):
           if int(self.facultyarr[j]) in self.keptarr:
            #print self.facultyarr[j]
            #import pdb; pdb.set_trace()
            #currlist[currnum]=Faculty(str(self.facultyarr[j]),float(self.resultarr[i,j]))
            currlist.append(Faculty(str(self.facultyarr[j]),float(self.resultarr[i,j]/self.validkeys[0,i])))
            currnum=currnum+1
           #currlist[j]=Faculty(self.facultyarr[j],self.resultarr[i,j]/self.validkeys[0,i])
           #currlist[j]=Faculty("sdf",self.resultarr[i,j])
          print  currnum
          sortfaculty=sorted(currlist, key=lambda Faculty: Faculty.matchingdegree,reverse=True)
          for j in sortfaculty:
            returnstr= returnstr+j.printfaculty()+"\n"
           
         else: 
            returnstr= "no corresponding term in the dictionary"
          
        return returnstr
       
   
    
    def getfaculty(self):
        self.facultyarr=[]
       # for j in self.Facultyind:
        self.cursor.execute("SELECT col0 FROM doc_pairwise_cosine_matrix;")
        # specify the type to select  at first can further improve efficiency 
        self.facultynum=int (self.cursor.rowcount)
        for i in self.cursor:
             self.facultyarr.append(i[0])
def procthread(connection, lsiobj,addr): 
      locallsi=lsiobj  
      loop=0 
      content=""   
      while content!="ack":
        print str(addr)+"   working"
        try:
          content= connection.recv(1024)
          if not content:break
          print "received   "+content
          
          if  '|' in content:
            pack=content.split('|')
          
            resultstr=lsiobj.queryA(pack[1],pack[0])
            connection.sendall(resultstr+"\0")
            print "message sent" + resultstr
        except Exception,e:
           print "dropping connection"
           return
      print str(addr)+"   quiting"    
            
       
opts, args=getopt.getopt(sys.argv[1:],'h') 
db =  MySQLdb.connect("localhost","root","baseg","parsingdata")
cursor = db.cursor()
db2 =  MySQLdb.connect("localhost","root","baseg","collaboratum")
cursor2 = db2.cursor()
mylsa = LSA(stopwords, ignorechars,args,cursor,cursor2)
mylsa.getfaculty()
mylsa.parse()
HOST = ''                 # Symbolic name meaning all available interfaces
PORT = 50004              # Arbitrary non-privileged port
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
s.bind((HOST, PORT))
s.listen(5)
print "server start listenning"
#mylsa.writearr()
while 1:
 
 conn, addr = s.accept()
 print "accept connection from "+ str(addr)
 t=threading.Thread(target=procthread,args=(conn,mylsa,addr))
 t.start() 
s.close()  
  
#mylsa.build()

db.close()
db2.close()
