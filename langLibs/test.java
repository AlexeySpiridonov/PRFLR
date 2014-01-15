public class Program {
	public static void main(String[] args){
		try {
			PRFLP.init("192.168.1.45-testAppn", "testKey");
		} catch (Exception e) {
			e.printStackTrace();
		}
		PRFLP.overflowCount = 50;
		PRFLP.begin("mongoDB.save");
		for(int i = 0; i < 10; i++) {
			PRFLP.begin("mongoDB.save step" + i);
			try {
				Thread.sleep(10);
			} catch (InterruptedException e1) {
				e1.printStackTrace();
			}
			try {
				PRFLP.end("mongoDB.save step" + i, "step " + i);
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
		try {
			PRFLP.end("mongoDB.save", "Good!");
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
}
