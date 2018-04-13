import time, threading, os, math ,sys,redis
r = redis.Redis(host='127.0.0.1', port=6379)
WechatNewsQueue = 'WechatNewsQueue'
WechatNewsKey_total = r.llen(WechatNewsQueue)
threading_total = 50
localtime = time.strftime("%m-%d %H:%M:%S", time.localtime())
file_lock = "Thread_@"

if(r.get(file_lock)):
    print("%s py file lock" %(localtime))
    sys.exit()

if( WechatNewsKey_total <= 0 ):
    print("%s py not data" %(localtime))
    sys.exit()

r.set(file_lock,1)
print("%s py start threading " %(localtime))
def loop():
        os.system('/opt/php/bin/php /opt/tuanzhongy/Applications/Api/Events/WxNewsThread.php')

def thread_(total):
        for n in range(total):
            t = threading.Thread(target=loop)
            time.sleep(0.2)
            t.setDaemon(True)
            t.start()
        t.join()

for_count = math.ceil(WechatNewsKey_total/threading_total)

if( WechatNewsKey_total < threading_total ) :
	threading_total = WechatNewsKey_total

cha = 0
for i in range(for_count):
		threading_n = 0
		cha = threading_total*(i+1)-WechatNewsKey_total
		if( cha <= 0 ):
			threading_n = threading_total
		else:
			threading_n = threading_total-cha
		print("threading %u" %(threading_n) )
		thread_(threading_n)

r.delete(file_lock)